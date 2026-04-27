<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {--max-kb=90 : Taille max cible en Ko}
        {--max-width=800 : Largeur max en px}
        {--quality=80 : Qualite JPEG (1-100)}
        {--dry-run : Compter sans modifier}
        {--dir=products : Sous-dossier de storage/app/public/}';

    protected $description = 'Compresse et redimensionne les images produit pour le SEO';

    public function handle(): int
    {
        $maxKb = (int) $this->option('max-kb');
        $maxWidth = (int) $this->option('max-width');
        $quality = (int) $this->option('quality');
        $dryRun = $this->option('dry-run');
        $subDir = $this->option('dir');

        $dir = storage_path('app/public/' . $subDir);

        if (! is_dir($dir)) {
            $this->error("Dossier non trouve : {$dir}");
            return self::FAILURE;
        }

        // Compter les fichiers a traiter
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if (! preg_match('/\.(jpg|jpeg|png)$/i', $file->getFilename())) continue;
            if ($file->getSize() / 1024 <= $maxKb) continue;
            $files[] = $file->getPathname();
        }

        $this->info(count($files) . " images > {$maxKb} Ko a optimiser");

        if ($dryRun) {
            $totalBefore = array_sum(array_map('filesize', $files));
            $this->info('Taille actuelle : ' . round($totalBefore / 1024 / 1024) . ' Mo');
            return self::SUCCESS;
        }

        if (! extension_loaded('gd')) {
            $this->error('Extension GD requise. Activez-la dans php.ini.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $saved = 0;
        $totalBefore = 0;
        $totalAfter = 0;
        $errors = 0;

        foreach ($files as $path) {
            $bar->advance();
            $sizeBefore = filesize($path);
            $totalBefore += $sizeBefore;

            try {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                // Detecter le vrai type MIME (certains PNG sont renommes en .jpg)
                $mime = @mime_content_type($path);
                $realType = match (true) {
                    str_contains($mime ?? '', 'png') => 'png',
                    str_contains($mime ?? '', 'webp') => 'webp',
                    default => 'jpg',
                };

                // Charger l'image selon le vrai type
                $img = match ($realType) {
                    'png' => @imagecreatefrompng($path),
                    'webp' => @imagecreatefromwebp($path),
                    default => @imagecreatefromjpeg($path),
                };

                if (! $img) {
                    $errors++;
                    continue;
                }

                $w = imagesx($img);
                $h = imagesy($img);

                // Redimensionner si trop large
                if ($w > $maxWidth) {
                    $newH = (int) ($h * $maxWidth / $w);
                    $resized = imagecreatetruecolor($maxWidth, $newH);

                    // Conserver la transparence pour PNG
                    if ($ext === 'png') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                    }

                    imagecopyresampled($resized, $img, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);
                    imagedestroy($img);
                    $img = $resized;
                }

                // Sauvegarder en JPEG compresse (meme les PNG, sauf transparence)
                if ($realType === 'png' || $ext === 'png') {
                    // Convertir PNG en JPEG pour gagner en taille
                    $jpgPath = preg_replace('/\.png$/i', '.jpg', $path);
                    $bg = imagecreatetruecolor(imagesx($img), imagesy($img));
                    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                    imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
                    imagejpeg($bg, $path, $quality);
                    imagedestroy($bg);
                } else {
                    imagejpeg($img, $path, $quality);
                }

                imagedestroy($img);

                // Si encore trop gros, reduire la qualite
                $sizeAfter = filesize($path);
                if ($sizeAfter / 1024 > $maxKb) {
                    $img = imagecreatefromjpeg($path);
                    if ($img) {
                        $lowerQuality = max(40, $quality - 20);
                        imagejpeg($img, $path, $lowerQuality);
                        imagedestroy($img);
                        $sizeAfter = filesize($path);
                    }
                }

                $totalAfter += $sizeAfter;
                $saved++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $savedMb = round(($totalBefore - $totalAfter) / 1024 / 1024);
        $this->info("Optimise: {$saved} images");
        $this->info("Erreurs: {$errors}");
        $this->info("Avant: " . round($totalBefore / 1024 / 1024) . " Mo");
        $this->info("Apres: " . round($totalAfter / 1024 / 1024) . " Mo");
        $this->info("Economise: {$savedMb} Mo");

        return self::SUCCESS;
    }
}
