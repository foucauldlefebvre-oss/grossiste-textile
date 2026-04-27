<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;

class ExpireQuotes extends Command
{
    protected $signature = 'quotes:expire';

    protected $description = 'Mark sent quotes past their expiration date as expired';

    public function handle(): int
    {
        $expired = Quote::expired()->get();

        if ($expired->isEmpty()) {
            $this->info('Aucun devis a expirer.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($expired as $quote) {
            $quote->update(['status' => 'expired']);
            $count++;
        }

        $this->info("{$count} devis expire(s).");

        return self::SUCCESS;
    }
}
