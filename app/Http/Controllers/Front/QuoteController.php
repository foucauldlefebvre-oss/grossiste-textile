<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
    public function show(string $reference)
    {
        $quote = Quote::where('reference', $reference)
            ->with(['items.product', 'items.color', 'items.size', 'items.technique', 'user'])
            ->firstOrFail();

        return view('front.quote.show', compact('quote'));
    }

    public function download(string $reference)
    {
        $quote = Quote::where('reference', $reference)->firstOrFail();

        if (! $quote->bat_pdf || ! Storage::disk('public')->exists($quote->bat_pdf)) {
            abort(404, 'PDF non disponible');
        }

        return Storage::disk('public')->download(
            $quote->bat_pdf,
            "devis-{$quote->reference}.pdf"
        );
    }
}
