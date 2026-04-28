<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Services\CartService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaintenanceCleanup extends Command
{
    protected $signature = 'maintenance:cleanup
        {--dry-run : Afficher sans supprimer}';

    protected $description = 'Nettoyage automatique : sessions, cache, logs, fichiers commandes archivees';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('=== DRY RUN ===');
        }

        $this->cleanSessions($dryRun);
        $this->cleanCache($dryRun);
        $this->cleanLogs($dryRun);
        $this->cleanArchivedOrderFiles($dryRun);
        $this->abandonStaleCarts($dryRun);
        $this->cleanOldNotifications($dryRun);
        $this->cleanOldVisits($dryRun);
        $this->cleanOldChats($dryRun);

        $this->newLine();
        $this->info('Nettoyage termine.');

        return self::SUCCESS;
    }

    /**
     * Purge les sessions expirees (> 7 jours).
     */
    private function cleanSessions(bool $dryRun): void
    {
        $cutoff = now()->subDays(7)->getTimestamp();
        $count = DB::table('sessions')->where('last_activity', '<', $cutoff)->count();
        $this->info("Sessions expirees : {$count}");

        if (! $dryRun && $count > 0) {
            DB::table('sessions')->where('last_activity', '<', $cutoff)->delete();
        }
    }

    /**
     * Purge le cache DB expire.
     */
    private function cleanCache(bool $dryRun): void
    {
        if (DB::getSchemaBuilder()->hasTable('cache')) {
            $count = DB::table('cache')->where('expiration', '<', now()->getTimestamp())->count();
            $this->info("Entrees cache expirees : {$count}");

            if (! $dryRun && $count > 0) {
                DB::table('cache')->where('expiration', '<', now()->getTimestamp())->delete();
            }
        }
    }

    /**
     * Tronque les fichiers de log > 10 Mo et supprime ceux > 30 jours.
     */
    private function cleanLogs(bool $dryRun): void
    {
        $logDir = storage_path('logs');
        $cleaned = 0;
        $deleted = 0;

        foreach (glob("{$logDir}/*.log") as $logFile) {
            $sizeMb = filesize($logFile) / 1024 / 1024;
            $ageDays = (time() - filemtime($logFile)) / 86400;

            // Supprimer les logs de + de 30 jours
            if ($ageDays > 30 && basename($logFile) !== 'laravel.log') {
                if (! $dryRun) {
                    unlink($logFile);
                }
                $deleted++;

                continue;
            }

            // Tronquer les logs > 10 Mo (garder les 1000 dernieres lignes)
            if ($sizeMb > 10) {
                if (! $dryRun) {
                    $lines = file($logFile);
                    $keep = array_slice($lines, -1000);
                    file_put_contents($logFile, implode('', $keep));
                }
                $cleaned++;
            }
        }

        $this->info("Logs : {$cleaned} tronques, {$deleted} supprimes");
    }

    /**
     * Supprime les fichiers (BAT, fichiers clients, devis PDF) des commandes
     * livrees ou annulees depuis plus de 60 jours.
     * Conserve : factures, detail commande en DB.
     */
    private function cleanArchivedOrderFiles(bool $dryRun): void
    {
        $cutoff = now()->subDays(60);

        // Commandes livrees (shipped_at > 60j) ou annulees (updated_at > 60j)
        $orders = Order::where(function ($q) use ($cutoff) {
            $q->where('status', 'delivered')->where('delivered_at', '<', $cutoff);
            $q->orWhere(function ($q2) use ($cutoff) {
                $q2->where('status', 'shipped')->where('shipped_at', '<', $cutoff);
            });
            $q->orWhere(function ($q2) use ($cutoff) {
                $q2->where('status', 'cancelled')->where('updated_at', '<', $cutoff);
            });
        })->pluck('id');

        if ($orders->isEmpty()) {
            $this->info('Fichiers commandes archivees : aucune commande eligible');

            return;
        }

        // Recuperer les documents a supprimer (tout sauf factures)
        $documents = OrderDocument::whereIn('order_id', $orders)
            ->whereNotIn('type', ['invoice'])
            ->get();

        $filesDeleted = 0;
        $spaceFreed = 0;

        foreach ($documents as $doc) {
            if ($doc->path && Storage::disk('public')->exists($doc->path)) {
                $spaceFreed += Storage::disk('public')->size($doc->path);
                if (! $dryRun) {
                    Storage::disk('public')->delete($doc->path);
                }
                $filesDeleted++;
            }

            if (! $dryRun) {
                $doc->delete();
            }
        }

        $spaceMb = round($spaceFreed / 1024 / 1024, 1);
        $this->info("Fichiers commandes archivees : {$filesDeleted} fichiers ({$spaceMb} Mo) sur {$orders->count()} commandes");
    }

    /**
     * Marque les paniers actifs sans modif depuis 30j comme "abandoned".
     * Les paniers abandonnés peuvent être supprimés au-delà de 90j.
     */
    private function abandonStaleCarts(bool $dryRun): void
    {
        $cutoffAbandon = now()->subDays(30);
        $cutoffDelete = now()->subDays(90);

        $toAbandon = Cart::active()->where('updated_at', '<', $cutoffAbandon)->count();
        $this->info("Paniers actifs sans activité > 30j (à abandonner) : {$toAbandon}");

        if (! $dryRun && $toAbandon > 0) {
            app(CartService::class)->abandonStaleCarts(30);
        }

        $oldAbandoned = Cart::abandoned()->where('updated_at', '<', $cutoffDelete)->count();
        $this->info("Paniers abandonnés > 90j (à supprimer) : {$oldAbandoned}");

        if (! $dryRun && $oldAbandoned > 0) {
            Cart::abandoned()->where('updated_at', '<', $cutoffDelete)->delete();
        }
    }

    /**
     * Purge les notification_logs de plus de 60 jours.
     */
    private function cleanOldNotifications(bool $dryRun): void
    {
        if (! DB::getSchemaBuilder()->hasTable('notification_logs')) {
            return;
        }

        $cutoff = now()->subDays(60);
        $count = DB::table('notification_logs')->where('created_at', '<', $cutoff)->count();
        $this->info("Notification logs > 60j : {$count}");

        if (! $dryRun && $count > 0) {
            DB::table('notification_logs')->where('created_at', '<', $cutoff)->delete();
        }
    }

    /**
     * Purge les visites de plus de 6 mois.
     */
    private function cleanOldVisits(bool $dryRun): void
    {
        if (! DB::getSchemaBuilder()->hasTable('visits')) {
            return;
        }

        $cutoff = now()->subMonths(6);
        $count = DB::table('visits')->where('visited_at', '<', $cutoff)->count();
        $this->info("Visites > 6 mois : {$count}");

        if (! $dryRun && $count > 0) {
            DB::table('visits')->where('visited_at', '<', $cutoff)->delete();
        }
    }

    /**
     * Purge les conversations chat de plus de 30 jours.
     */
    private function cleanOldChats(bool $dryRun): void
    {
        if (! DB::getSchemaBuilder()->hasTable('chat_conversations')) {
            return;
        }

        $cutoff = now()->subDays(30);
        $count = DB::table('chat_conversations')->where('created_at', '<', $cutoff)->count();
        $this->info("Conversations chat > 30j : {$count}");

        if (! $dryRun && $count > 0) {
            $ids = DB::table('chat_conversations')->where('created_at', '<', $cutoff)->pluck('id');
            DB::table('chat_messages')->whereIn('chat_conversation_id', $ids)->delete();
            DB::table('chat_conversations')->whereIn('id', $ids)->delete();
        }
    }
}
