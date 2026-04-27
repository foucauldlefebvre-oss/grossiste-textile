<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderNotificationService;
use Illuminate\Http\Request;

class OrderBatController extends Controller
{
    public function review(string $token)
    {
        $order = Order::where('bat_token', $token)
            ->with(['batDocuments', 'user', 'items.product'])
            ->firstOrFail();

        return view('order-bat.review', compact('order'));
    }

    public function approve(Request $request, string $token)
    {
        $order = Order::where('bat_token', $token)->firstOrFail();

        $order->update([
            'bat_status' => 'approved',
            'bat_client_decided_at' => now(),
            'status_bat' => 'done',
            'bat_done_at' => now(),
        ]);

        app(OrderNotificationService::class)->notifyBatApproved($order);

        return back()->with('bat-success', 'BAT approuve ! La production va pouvoir commencer.');
    }

    public function requestRevision(Request $request, string $token)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ], [
            'comment.required' => 'Veuillez indiquer les modifications souhaitees.',
        ]);

        $order = Order::where('bat_token', $token)->firstOrFail();

        $order->update([
            'bat_status' => 'revision_requested',
            'bat_client_comment' => $validated['comment'],
            'bat_client_decided_at' => now(),
        ]);

        app(OrderNotificationService::class)->notifyBatRevisionRequested($order);

        return back()->with('bat-success', 'Votre demande de modification a ete envoyee.');
    }
}
