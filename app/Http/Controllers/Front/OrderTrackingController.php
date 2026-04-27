<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderTrackingController extends Controller
{
    public function show(string $orderNumber)
    {
        $order = Order::where('reference', $orderNumber)
            ->with(['items.product', 'documents' => fn ($q) => $q->where('visible_to_client', true)])
            ->firstOrFail();

        return view('front.order.tracking', compact('order'));
    }
}
