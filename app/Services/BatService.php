<?php

namespace App\Services;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class BatService
{
    /**
     * Generate the BAT PDF for a quote and store it.
     */
    public function generate(Quote $quote): string
    {
        $quote->load([
            'user',
            'items.product',
            'items.color',
            'items.size',
            'items.technique',
        ]);

        $pdf = Pdf::loadView('pdf.bat', compact('quote'))
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);

        $filename = 'bats/BAT-' . $quote->reference . '.pdf';
        $path = storage_path('app/private/' . $filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf->save($path);

        $quote->update([
            'bat_pdf' => $filename,
            'bat_status' => 'pending',
        ]);

        return $filename;
    }

    /**
     * Generate a unique token and mark BAT as sent.
     */
    public function send(Quote $quote): string
    {
        if (! $quote->bat_pdf) {
            $this->generate($quote);
        }

        $token = Str::random(48);

        $quote->update([
            'bat_token' => $token,
            'bat_status' => 'sent',
            'bat_sent_at' => now(),
        ]);

        // Send BAT ready notification
        app(NotificationService::class)->sendBatReady($quote);

        return $token;
    }

    /**
     * Get the public approval URL for a quote's BAT.
     */
    public function getApprovalUrl(Quote $quote): ?string
    {
        if (! $quote->bat_token) {
            return null;
        }

        return route('bat.show', $quote->bat_token);
    }

    /**
     * Approve a BAT.
     */
    public function approve(Quote $quote): void
    {
        $quote->update([
            'bat_status' => 'approved',
            'bat_rejection_reason' => null,
        ]);
    }

    /**
     * Reject a BAT with a reason.
     */
    public function reject(Quote $quote, ?string $reason = null): void
    {
        $quote->update([
            'bat_status' => 'rejected',
            'bat_rejection_reason' => $reason,
        ]);
    }

    /**
     * Download the BAT PDF as a response.
     */
    public function download(Quote $quote)
    {
        if (! $quote->bat_pdf) {
            $this->generate($quote);
            $quote->refresh();
        }

        $path = storage_path('app/private/' . $quote->bat_pdf);

        if (! file_exists($path)) {
            $this->generate($quote);
            $quote->refresh();
            $path = storage_path('app/private/' . $quote->bat_pdf);
        }

        return response()->download($path, 'BAT-' . $quote->reference . '.pdf');
    }

    /**
     * Stream the BAT PDF inline.
     */
    public function stream(Quote $quote)
    {
        $quote->load([
            'user',
            'items.product',
            'items.color',
            'items.size',
            'items.technique',
        ]);

        return Pdf::loadView('pdf.bat', compact('quote'))
            ->setPaper('a4')
            ->stream('BAT-' . $quote->reference . '.pdf');
    }
}
