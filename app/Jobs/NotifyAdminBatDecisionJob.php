<?php

namespace App\Jobs;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminBatDecisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
    ) {}

    public function handle(): void
    {
        $adminEmails = config('bat.admin_emails', []);

        if (empty($adminEmails)) {
            Log::warning("NotifyAdminBatDecisionJob: aucun admin_emails configuré");

            return;
        }

        $statusLabel = match ($this->quote->bat_status) {
            'approved' => 'APPROUVÉ',
            'rejected' => 'REFUSÉ',
            'revision_requested' => 'MODIFICATION DEMANDÉE',
            default => strtoupper($this->quote->bat_status),
        };

        $comment = $this->quote->bat_client_comment
            ? "\nCommentaire client : {$this->quote->bat_client_comment}"
            : '';

        $body = "Le BAT du devis {$this->quote->reference} a été {$statusLabel} par le client.{$comment}";

        foreach ($adminEmails as $email) {
            Mail::raw($body, function ($message) use ($email, $statusLabel) {
                $message->to($email)
                    ->subject("BAT {$statusLabel} — Devis {$this->quote->reference}");
            });
        }

        Log::info("NotifyAdminBatDecisionJob: devis {$this->quote->reference} — {$statusLabel}");
    }
}
