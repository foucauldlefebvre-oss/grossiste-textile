<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $cart = Cart::with('items')->active()->where('user_id', auth()->id())->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart')->with('cart-error', 'Votre panier est vide.');
        }

        $order = app(OrderService::class)->createFromCart($cart);

        if (config('stripe.secret') === 'sk_test_XXXX' || ! config('stripe.secret')) {
            return redirect()->route('payment.success', ['order' => $order->reference]);
        }

        try {
            $session = app(PaymentService::class)->createCheckoutSession($order);
            return redirect()->away($session->url);
        } catch (\Exception $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return redirect()->route('cart')->with('cart-error', 'Erreur lors de la creation du paiement. Veuillez reessayer.');
        }
    }

    public function success(Request $request, string $order)
    {
        $order = Order::where('reference', $order)->firstOrFail();

        // Systempay POST return: process kr-answer
        if ($request->has('kr-answer')) {
            $krAnswer = json_decode($request->input('kr-answer'), true);
            $orderStatus = $krAnswer['orderStatus'] ?? null;

            if ($orderStatus === 'PAID' && $order->payment_status === 'pending') {
                $amountPaid = ($krAnswer['orderDetails']['orderTotalAmount'] ?? 0) / 100;
                $transId = $krAnswer['transactions'][0]['uuid'] ?? null;
                app(OrderService::class)->markAsPaid($order, $transId, $amountPaid);
                $order->refresh();
            }
        }

        // Stripe fallback
        if ($order->payment_status === 'pending' && $request->has('session_id')) {
            try {
                $paymentService = app(PaymentService::class);
                $paymentService->handlePaymentSuccess($request->session_id);
                $order->refresh();
            } catch (\Exception $e) {
                // Webhook will handle it
            }
        }

        return view('payment.success', compact('order'));
    }

    public function cancel(string $order)
    {
        $order = Order::where('reference', $order)->firstOrFail();

        return view('payment.cancel', compact('order'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $paymentService = app(PaymentService::class);
            $event = $paymentService->constructWebhookEvent($payload, $signature);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $paymentService->handlePaymentSuccess($session->id);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
