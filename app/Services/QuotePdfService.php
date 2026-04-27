<?php

namespace App\Services;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QuotePdfService
{
    public function generate(Quote $quote): string
    {
        $quote->load([
            'items.product',
            'items.color',
            'items.size',
            'items.technique',
            'user',
        ]);

        $markings = $quote->markings ?? [];
        $firstVal = ! empty($markings) ? reset($markings) : null;
        $isGrouped = is_array($firstVal) && (empty($firstVal) || ! isset($firstVal['size']));

        $pdf = Pdf::loadView('pdf.quote', compact('quote', 'markings', 'isGrouped'))
            ->setPaper('a4')
            ->setOption('isPhpEnabled', true);

        $filename = 'quotes/' . $quote->reference . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        $quote->update(['bat_pdf' => $filename]);

        return $filename;
    }

    public function download(Quote $quote): \Symfony\Component\HttpFoundation\Response
    {
        if (! $quote->bat_pdf || ! Storage::disk('public')->exists($quote->bat_pdf)) {
            $this->generate($quote);
            $quote->refresh();
        }

        return Storage::disk('public')->download(
            $quote->bat_pdf,
            "devis-{$quote->reference}.pdf"
        );
    }
}
