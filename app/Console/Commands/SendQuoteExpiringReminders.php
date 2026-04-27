<?php

namespace App\Console\Commands;

use App\Models\Quote;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendQuoteExpiringReminders extends Command
{
    protected $signature = 'quotes:remind';

    protected $description = 'Send email reminders for quotes expiring in 7, 2, or 1 day(s)';

    public function handle(): int
    {
        $service = app(NotificationService::class);
        $sent = 0;

        foreach ([7, 2, 1] as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $quotes = Quote::whereIn('status', ['sent', 'draft'])
                ->whereNotNull('user_id')
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', $targetDate)
                ->with('user')
                ->get();

            foreach ($quotes as $quote) {
                // Avoid sending duplicates
                $alreadySent = \App\Models\NotificationLog::where('type', 'quote_expiring_' . $days . 'd')
                    ->where('subject', 'like', '%' . $quote->reference . '%')
                    ->where('status', 'sent')
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $service->sendQuoteExpiring($quote, $days);
                $sent++;
                $this->info("Relance J-{$days} envoyee pour {$quote->reference} a {$quote->user->email}");
            }
        }

        $this->info("Total: {$sent} relances envoyees.");

        return self::SUCCESS;
    }
}
