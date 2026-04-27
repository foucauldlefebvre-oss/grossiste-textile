<?php

namespace App\Console\Commands;

use App\Helpers\ColorHelper;
use App\Models\ProductColor;
use Illuminate\Console\Command;

class FixColorHex extends Command
{
    protected $signature = 'colors:fix-hex {--dry-run : Show changes without applying}';

    protected $description = 'Fill missing hex_code on product colors using the color name mapping';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $missing = ProductColor::where(function ($q) {
            $q->whereNull('hex_code')->orWhere('hex_code', '');
        })->get();

        $this->info("Colors missing hex_code: {$missing->count()}");

        $fixed = 0;
        $stillMissing = [];

        foreach ($missing as $color) {
            $hex = ColorHelper::resolve($color->name);

            if ($hex) {
                $fixed++;
                if (! $dryRun) {
                    $color->update(['hex_code' => $hex]);
                }
            } else {
                $stillMissing[$color->name] = ($stillMissing[$color->name] ?? 0) + 1;
            }
        }

        $this->info(($dryRun ? '[DRY-RUN] Would fix' : 'Fixed') . ": {$fixed} colors");

        if (! empty($stillMissing)) {
            arsort($stillMissing);
            $this->warn('Still missing (' . count($stillMissing) . ' distinct names):');
            $rows = [];
            foreach (array_slice($stillMissing, 0, 40) as $name => $count) {
                $rows[] = [$name, $count];
            }
            $this->table(['Color Name', 'Count'], $rows);
        }

        return self::SUCCESS;
    }
}
