<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $stats = [
            'orders' => $user->orders()->count(),
            'quotes' => $user->quotes()->count(),
            'pending_orders' => $user->orders()->whereIn('status', ['pending', 'confirmed', 'in_production'])->count(),
        ];

        $recentOrders = $user->orders()
            ->latest()
            ->limit(5)
            ->get();

        return view('account.dashboard', compact('stats', 'recentOrders'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()
            ->latest()
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function orderShow(string $reference)
    {
        $order = auth()->user()->orders()
            ->where('reference', $reference)
            ->with(['items.product', 'items.color', 'items.size', 'items.technique', 'invoice', 'documents'])
            ->firstOrFail();

        return view('account.order-show', compact('order'));
    }

    public function downloadInvoice(string $reference)
    {
        $order = auth()->user()->orders()
            ->where('reference', $reference)
            ->with('invoice')
            ->firstOrFail();

        abort_unless($order->invoice && $order->invoice->pdf_path, 404);

        return response()->download(
            storage_path('app/private/' . $order->invoice->pdf_path),
            'Facture-' . $order->invoice->number . '.pdf'
        );
    }

    public function invoices()
    {
        $invoices = Invoice::where('user_id', auth()->id())
            ->where('issued_at', '>=', now()->subMonths(18))
            ->with('order:id,reference')
            ->orderByDesc('issued_at')
            ->paginate(15);

        return view('account.invoices', compact('invoices'));
    }

    public function downloadInvoiceById(Invoice $invoice)
    {
        abort_unless($invoice->user_id === auth()->id(), 403);

        return app(InvoiceService::class)->download($invoice);
    }

    public function quotes()
    {
        $quotes = auth()->user()->quotes()
            ->latest()
            ->paginate(10);

        return view('account.quotes', compact('quotes'));
    }

    public function addresses()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function profile()
    {
        return view('account.profile');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'company' => ['nullable', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:17'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        auth()->user()->update($validated);

        return back()->with('success', 'Profil mis a jour.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        auth()->user()->update([
            'password' => bcrypt($request->password),
        ]);

        return back()->with('success', 'Mot de passe mis a jour.');
    }

    public function exportData()
    {
        $user = auth()->user();

        $data = [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'addresses' => $user->addresses->map(fn ($a) => $a->only([
                'label', 'first_name', 'last_name', 'company',
                'address_line_1', 'address_line_2', 'postal_code',
                'city', 'country', 'phone',
            ]))->toArray(),
            'orders' => $user->orders->map(fn ($o) => [
                'reference' => $o->reference,
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'total_ttc' => $o->total_ttc,
                'created_at' => $o->created_at->toIso8601String(),
            ])->toArray(),
            'quotes' => $user->quotes->map(fn ($q) => [
                'reference' => $q->reference,
                'status' => $q->status,
                'total_ttc' => $q->total_ttc,
                'created_at' => $q->created_at->toIso8601String(),
            ])->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        $filename = 'mes-donnees-' . date('Y-m-d') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = auth()->user();

        // Anonymize orders (keep for accounting)
        $user->orders()->update(['user_id' => null]);

        // Delete personal data
        $user->addresses()->delete();
        $user->quotes()->where('status', 'draft')->delete();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('home')->with('account-deleted', 'Votre compte a ete supprime.');
    }
}
