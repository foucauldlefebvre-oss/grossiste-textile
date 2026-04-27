<?php

namespace App\Http\Controllers;

use App\Helpers\BatHelper;
use App\Jobs\NotifyAdminBatDecisionJob;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BatController extends Controller
{
    public function show(string $token)
    {
        $quote = Quote::where('bat_token', $token)
            ->whereIn('bat_status', ['sent', 'approved', 'revision_requested', 'rejected'])
            ->with([
                'items.product',
                'items.color',
                'items.technique',
                'user',
            ])
            ->firstOrFail();

        // Auto-build templates JSON: rebuild if missing, old format,
        // group count mismatch, or all defaults (failed detection)
        $current = $quote->bat_svg_template;
        $existingTemplates = json_decode($current ?? '', true);
        $itemGroupIds = $quote->items->pluck('marking_group')->unique()->sort()->values()->all();
        $needsRebuild = ! $current
            || ! is_array($existingTemplates)
            || count($existingTemplates) !== count($itemGroupIds);

        // Also rebuild if all templates are tshirt-homme (likely failed detection)
        if (! $needsRebuild && is_array($existingTemplates) && $quote->items->isNotEmpty()) {
            $allDefault = collect($existingTemplates)->every(fn ($t) => $t === 'tshirt-homme');
            if ($allDefault) {
                $needsRebuild = true;
            }
        }

        if ($needsRebuild) {
            $quote->bat_svg_template = BatHelper::buildTemplatesJson($quote);
            $quote->saveQuietly();
        }

        return view('bat.review', compact('quote'));
    }

    public function save(Request $request, string $token)
    {
        $quote = Quote::where('bat_token', $token)->firstOrFail();

        $validated = $request->validate([
            'logo' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,ai,eps,psd',
            'logo_x' => 'nullable|numeric|min:0|max:30',
            'logo_y' => 'nullable|numeric|min:0|max:30',
            'logo_width' => 'nullable|numeric|min:0.5|max:30',
            'logo_height' => 'nullable|numeric|min:0.5|max:30',
        ]);

        $updates = [];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $dir = "bat-logos/{$token}";
            $ext = strtolower($file->getClientOriginalExtension());

            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                // Direct image — store as-is
                $path = $file->storeAs($dir, "logo.{$ext}", 'public');
                $updates['bat_logo_path'] = $path;
            } else {
                // Non-image — store then try ImageMagick conversion
                $path = $file->storeAs($dir, "logo.{$ext}", 'public');
                $fullPath = Storage::disk('public')->path($path);
                $pngPath = Storage::disk('public')->path("{$dir}/logo.png");
                $convertBin = PHP_OS_FAMILY === 'Windows' ? 'magick convert' : 'convert';
                $cmd = $convertBin . ' ' . escapeshellarg($fullPath) . '[0] ' . escapeshellarg($pngPath) . ' 2>&1';
                shell_exec($cmd);

                if (file_exists($pngPath)) {
                    $updates['bat_logo_path'] = "{$dir}/logo.png";
                } else {
                    // Fallback: keep original
                    $updates['bat_logo_path'] = $path;
                }
            }
        }

        if (isset($validated['logo_x'])) {
            $updates['bat_logo_x'] = $validated['logo_x'];
        }
        if (isset($validated['logo_y'])) {
            $updates['bat_logo_y'] = $validated['logo_y'];
        }
        if (isset($validated['logo_width'])) {
            $updates['bat_logo_width'] = $validated['logo_width'];
        }
        if (isset($validated['logo_height'])) {
            $updates['bat_logo_height'] = $validated['logo_height'];
        }

        if (! empty($updates)) {
            $quote->update($updates);
        }

        $quote->refresh();

        return response()->json([
            'success' => true,
            'logo_url' => $quote->bat_logo_path ? Storage::url($quote->bat_logo_path) : null,
            'logo_x' => $quote->bat_logo_x,
            'logo_y' => $quote->bat_logo_y,
            'logo_width' => $quote->bat_logo_width,
            'logo_height' => $quote->bat_logo_height,
        ]);
    }

    public function decide(Request $request, string $token)
    {
        $quote = Quote::where('bat_token', $token)->firstOrFail();

        $validated = $request->validate([
            'decision' => 'required|in:approved,revision_requested,rejected',
            'comment' => 'nullable|string|max:1000',
        ]);

        $quote->update([
            'bat_status' => $validated['decision'],
            'bat_client_comment' => $validated['comment'] ?? null,
            'bat_decided_at' => now(),
        ]);

        NotifyAdminBatDecisionJob::dispatch($quote);

        return redirect()->route('bat.confirmed', $token);
    }

    public function confirmed(string $token)
    {
        $quote = Quote::where('bat_token', $token)->firstOrFail();

        return view('bat.confirmed', compact('quote'));
    }
}
