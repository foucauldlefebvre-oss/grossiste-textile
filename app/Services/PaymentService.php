<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session for an order.
     */
    public function createCheckoutSession(Order $order): Session
    {
        $order->load('items.product');

        $lineItems = [];

        foreach ($order->items as $item) {
            $unitTotal = (float) $item->unit_price_ht + (float) $item->marking_price_ht;

            $lineItems[] = [
                'price_data' => [
                    'currency' => config('stripe.currency', 'eur'),
                    'unit_amount' => (int) round($unitTotal * 100), // cents
                    'product_data' => [
                        'name' => $item->product->name,
                        'description' => $this->buildItemDescription($item),
                    ],
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Add shipping as a line item if present
        if ($order->shipping_ht > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => config('stripe.currency', 'eur'),
                    'unit_amount' => (int) round((float) $order->shipping_ht * 100),
                    'product_data' => [
                        'name' => 'Frais de livraison',
                        'description' => $order->carrier ?? 'Livraison standard',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('payment.success', ['order' => $order->reference]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['order' => $order->reference]),
            'client_reference_id' => $order->reference,
            'customer_email' => $order->user?->email,
            'metadata' => [
                'order_id' => $order->id,
                'order_reference' => $order->reference,
            ],
            'locale' => 'fr',
        ]);
    }

    /**
     * Verify and parse a Stripe webhook event.
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('stripe.webhook_secret'),
        );
    }

    /**
     * Handle a successful payment from webhook.
     */
    public function handlePaymentSuccess(string $sessionId): void
    {
        $session = Session::retrieve($sessionId);

        $orderId = $session->metadata->order_id ?? null;
        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        app(OrderService::class)->markAsPaid($order, $session->payment_intent);
    }

    private function buildItemDescription($item): string
    {
        $parts = [];

        if ($item->color) {
            $parts[] = $item->color->name;
        }
        if ($item->size) {
            $parts[] = 'Taille ' . $item->size->size;
        }
        if ($item->technique) {
            $parts[] = $item->technique->name;
        }

        return implode(' - ', $parts) ?: 'Article';
    }
}
