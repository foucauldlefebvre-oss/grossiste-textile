<?php

namespace App\Livewire;

use App\Models\Quote;
use App\Services\MarkingRecommendationService;
use App\Services\QuoteService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class FloatingQuote extends Component
{
    public ?Quote $quote = null;
    public bool $open = false;

    /** Marking config per textile group (key = "{markingGroup}-{product_id}-{product_color_id}") */
    public array $groupTechniques = [];
    public array $groupZones = [];
    public array $groupVisualColors = [];

    /** Accordion expanded state per textile group */
    public array $expandedGroups = [];

    /** Expanded state for frozen marking groups (collapsed by default) */
    public array $expandedFrozenGroups = [];

    /** Logo configurations for the ACTIVE marking group only */
    public array $logos = [];

    /** Currently applied technique ID (for visual highlighting in recommendations) */
    public ?int $selectedRecommendationTechniqueId = null;

    /** Index of the logo currently being edited (expanded). null = last one or none */
    public ?int $activeLogoIndex = null;

    /**
     * All markings indexed by marking group number.
     * Structure: ["0" => [logos...], "1" => [logos...]]
     */
    public array $allGroupMarkings = [];

    public function mount(): void
    {
        $this->loadQuote();
    }

    #[On('item-added-to-quote')]
    public function onItemAdded(): void
    {
        $this->loadQuote();
        $this->open = true;
    }

    #[On('quote-updated')]
    public function onQuoteUpdated(): void
    {
        $this->loadQuote();
    }

    #[On('toggle-floating-quote')]
    public function toggle(): void
    {
        $this->loadQuote();
        $this->open = ! $this->open;
    }

    public function loadQuote(): void
    {
        $query = Quote::with([
            'items.product.techniqueRules.technique.constraint',
            'items.color',
            'items.size',
            'items.technique',
        ])->draft();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id')->where('session_id', session()->getId());
        }

        $this->quote = $query->first();
        $this->syncGroupState();
        $this->loadMarkings();
    }

    /**
     * Load markings from quote, supporting both old flat format and new grouped format.
     */
    private function loadMarkings(): void
    {
        $raw = $this->quote?->markings ?? [];

        // Detect format: old flat array of logos vs new grouped {"0": [...], "1": [...]}
        if (empty($raw)) {
            $this->allGroupMarkings = [];
            $this->logos = [];

            return;
        }

        // Detect format: new grouped {"0": [...], "1": [...]} vs old flat [{size, colors...}, ...]
        // PHP json_decode converts {"0": [...]} keys to integers, so we can't rely on is_string()
        $firstValue = reset($raw);
        if (is_array($firstValue) && (empty($firstValue) || ! isset($firstValue['size']))) {
            // New grouped format: each value is an array of logos (or empty array)
            $this->allGroupMarkings = array_map(fn ($v) => is_array($v) ? $v : [], $raw);
        } else {
            // Old flat format — assign all logos to group 0
            $this->allGroupMarkings = ['0' => $this->normalizeLogos($raw)];
        }

        // Active group's logos
        $activeGroup = (string) ($this->quote?->active_marking_group ?? 0);
        $this->logos = $this->normalizeLogos($this->allGroupMarkings[$activeGroup] ?? []);
    }

    /**
     * Ensure all logos have the expected flat structure.
     */
    private function normalizeLogos(array $logos): array
    {
        return array_values(array_map(function (array $logo) {
            if (isset($logo['placements']) && ! isset($logo['zone'])) {
                $first = $logo['placements'][0] ?? [];
                $logo['zone'] = $first['zone'] ?? '';
                $logo['different'] = $first['different'] ?? false;
                $logo['textiles'] = $first['textiles'] ?? [];
                unset($logo['placements']);
            }

            $base = [
                'type' => $logo['type'] ?? 'logo',
                'zone' => $logo['zone'] ?? '',
                'different' => $logo['different'] ?? false,
                'textiles' => $logo['textiles'] ?? [],
                'technique_id' => $logo['technique_id'] ?? null,
                'collapsed' => $logo['collapsed'] ?? false,
                'selected_label' => $logo['selected_label'] ?? null,
                'is_mix' => $logo['is_mix'] ?? false,
                'mix_techniques' => $logo['mix_techniques'] ?? [],
            ];

            if (($logo['type'] ?? 'logo') === 'name') {
                $base['name_size'] = $logo['name_size'] ?? 'petit';
                $base['name_lines'] = $logo['name_lines'] ?? '1';
            } else {
                $base['size'] = $logo['size'] ?? 'A4';
                $base['colors'] = (string) ($logo['colors'] ?? '1');
            }

            return $base;
        }, $logos));
    }

    /**
     * Initialize group state arrays from existing DB items.
     */
    private function syncGroupState(): void
    {
        if (! $this->quote || $this->quote->items->isEmpty()) {
            $this->groupTechniques = [];
            $this->groupZones = [];
            $this->groupVisualColors = [];
            $this->expandedGroups = [];

            return;
        }

        $newTechniques = [];
        $newZones = [];
        $newVisualColors = [];
        $newExpanded = [];

        foreach ($this->quote->items->groupBy(fn ($item) => $item->marking_group . '-' . $item->product_id . '-' . $item->product_color_id) as $groupKey => $items) {
            $first = $items->first();
            $newTechniques[$groupKey] = $first->marking_technique_id;
            $newZones[$groupKey] = $first->marking_zone;
            $newVisualColors[$groupKey] = $first->visual_colors ?? 1;
            $newExpanded[$groupKey] = $this->expandedGroups[$groupKey] ?? false;
        }

        $this->groupTechniques = $newTechniques;
        $this->groupZones = $newZones;
        $this->groupVisualColors = $newVisualColors;
        $this->expandedGroups = $newExpanded;

        // Restore selected technique for visual highlight (use active group's technique)
        $activeGroup = $this->quote->active_marking_group ?? 0;
        $activeTechniqueId = $this->quote->items
            ->where('marking_group', $activeGroup)
            ->whereNotNull('marking_technique_id')
            ->pluck('marking_technique_id')
            ->first();
        if ($activeTechniqueId) {
            $this->selectedRecommendationTechniqueId = $activeTechniqueId;
        }
    }

    #[Computed]
    public function activeMarkingGroup(): int
    {
        return $this->quote?->active_marking_group ?? 0;
    }

    public function isFrozen(int $markingGroup): bool
    {
        return $markingGroup < ($this->quote?->active_marking_group ?? 0);
    }

    /**
     * Computed: items organized by marking group, then by product+color within each.
     */
    #[Computed]
    public function markingGroups(): array
    {
        if (! $this->quote || $this->quote->items->isEmpty()) {
            return [];
        }

        $result = [];
        $activeGroup = $this->quote->active_marking_group ?? 0;

        foreach ($this->quote->items->groupBy('marking_group') as $markingGroupNum => $markingItems) {
            $frozen = $markingGroupNum < $activeGroup;
            $textileGroups = [];

            foreach ($markingItems->groupBy(fn ($item) => $item->product_id . '-' . $item->product_color_id) as $textileKey => $items) {
                $first = $items->first();
                $product = $first->product;
                $sortedItems = $items->sortBy(fn ($item) => $item->size?->sort_order ?? 0)->values();
                $groupKey = $markingGroupNum . '-' . $textileKey;

                $compatibleTechniques = $product->techniqueRules
                    ->where('is_compatible', true)
                    ->map(fn ($rule) => $rule->technique)
                    ->filter()
                    ->values();

                $availableZones = [];
                $selectedTechniqueId = $this->groupTechniques[$groupKey] ?? null;
                if ($selectedTechniqueId) {
                    $rule = $product->techniqueRules
                        ->where('marking_technique_id', $selectedTechniqueId)
                        ->first();
                    $availableZones = $rule?->marking_zones ?? [];
                }

                $maxColors = null;
                if ($selectedTechniqueId) {
                    $technique = $compatibleTechniques->firstWhere('id', $selectedTechniqueId);
                    $maxColors = $technique?->constraint?->max_colors;
                }

                $textileGroups[$groupKey] = [
                    'product' => $product,
                    'color' => $first->color,
                    'items' => $sortedItems,
                    'technique' => $first->technique,
                    'totalQuantity' => $items->sum('quantity'),
                    'groupTotal' => $items->sum('line_total_ht'),
                    'compatibleTechniques' => $compatibleTechniques,
                    'availableZones' => $availableZones,
                    'maxColors' => $maxColors,
                ];
            }

            // Get logos for this marking group
            $groupLogos = $this->allGroupMarkings[(string) $markingGroupNum] ?? [];

            $result[$markingGroupNum] = [
                'frozen' => $frozen,
                'textileGroups' => $textileGroups,
                'totalQuantity' => $markingItems->sum('quantity'),
                'groupTotal' => $markingItems->sum('line_total_ht'),
                'logos' => $groupLogos,
            ];
        }

        ksort($result);

        return $result;
    }

    /**
     * Backward-compatible: flat list of active textile groups for logos/recommendations.
     */
    #[Computed]
    public function groupedItems(): array
    {
        $all = [];
        $activeGroup = $this->quote?->active_marking_group ?? 0;
        foreach ($this->markingGroups as $mgNum => $mg) {
            if ($mgNum != $activeGroup) {
                continue;
            }
            foreach ($mg['textileGroups'] as $key => $tg) {
                $all[$key] = $tg;
            }
        }

        return $all;
    }

    /**
     * Freeze the current active marking group (textile + marquage together).
     */
    public function freezeCurrentGroup(): void
    {
        if (! $this->quote) {
            return;
        }

        $activeGroup = $this->quote->active_marking_group ?? 0;

        // Check there are items in the active group
        $hasItems = $this->quote->items()->where('marking_group', $activeGroup)->exists();
        if (! $hasItems) {
            return;
        }

        // Save current logos under the active group number
        $this->allGroupMarkings[(string) $activeGroup] = $this->logos;

        // Clear logos for the new active group
        $newActiveGroup = $activeGroup + 1;
        $this->allGroupMarkings[(string) $newActiveGroup] = [];

        // Persist all markings
        $this->quote->update([
            'active_marking_group' => $newActiveGroup,
            'markings' => $this->allGroupMarkings,
        ]);

        $this->logos = [];
        $this->selectedRecommendationTechniqueId = null;
        $this->loadQuote();
    }

    /**
     * Unfreeze a marking group (merge it back as active).
     */
    public function unfreezeGroup(int $markingGroup): void
    {
        if (! $this->quote) {
            return;
        }

        $activeGroup = $this->quote->active_marking_group ?? 0;

        $this->quote->items()
            ->where('marking_group', $markingGroup)
            ->update(['marking_group' => $activeGroup]);

        // Merge frozen group logos into active logos
        $frozenLogos = $this->allGroupMarkings[(string) $markingGroup] ?? [];
        unset($this->allGroupMarkings[(string) $markingGroup]);
        $this->logos = array_merge($this->logos, $frozenLogos);
        $this->allGroupMarkings[(string) $activeGroup] = $this->logos;

        $this->quote->update(['markings' => $this->allGroupMarkings]);
        $this->loadQuote();
    }

    public function toggleFrozenGroupExpanded(int $markingGroup): void
    {
        $this->expandedFrozenGroups[$markingGroup] = ! ($this->expandedFrozenGroups[$markingGroup] ?? false);
    }

    public function getGroupItems(string $groupKey): \Illuminate\Support\Collection
    {
        if (! $this->quote) {
            return collect();
        }

        $parts = explode('-', $groupKey);
        $markingGroup = (int) $parts[0];
        $productId = $parts[1];
        $colorId = $parts[2];

        return $this->quote->items->filter(
            fn ($item) => $item->marking_group == $markingGroup
                && $item->product_id == $productId
                && $item->product_color_id == $colorId
        );
    }

    public function toggleGroupExpanded(string $groupKey): void
    {
        $this->expandedGroups[$groupKey] = ! ($this->expandedGroups[$groupKey] ?? false);
    }

    public function updateGroupTechnique(string $groupKey, $techniqueId): void
    {
        $techniqueId = $techniqueId ? (int) $techniqueId : null;
        $this->groupTechniques[$groupKey] = $techniqueId;
        $this->groupZones[$groupKey] = null;
        $this->groupVisualColors[$groupKey] = 1;

        $this->applyGroupMarking($groupKey);
    }

    public function updateGroupZone(string $groupKey, ?string $zone): void
    {
        $this->groupZones[$groupKey] = $zone ?: null;
        $this->applyGroupMarking($groupKey);
    }

    public function updateGroupVisualColors(string $groupKey, int $colors): void
    {
        if ($colors < 1) {
            $colors = 1;
        }

        $groups = $this->groupedItems;
        if (isset($groups[$groupKey]) && $groups[$groupKey]['maxColors']) {
            $colors = min($colors, $groups[$groupKey]['maxColors']);
        }

        $this->groupVisualColors[$groupKey] = $colors;
        $this->applyGroupMarking($groupKey);
    }

    private function applyGroupMarking(string $groupKey): void
    {
        $items = $this->getGroupItems($groupKey);
        if ($items->isEmpty()) {
            return;
        }

        // Get visual format from the first logo
        $logo = $this->logos[0] ?? null;
        $visualFormat = $logo['size'] ?? 'A4';

        try {
            app(QuoteService::class)->updateGroupMarking(
                $items,
                $this->groupTechniques[$groupKey] ?? null,
                $this->groupZones[$groupKey] ?? null,
                $this->groupVisualColors[$groupKey] ?? 1,
                $visualFormat,
            );
            $this->loadQuote();
        } catch (\InvalidArgumentException $e) {
            session()->flash('floating-quote-error', $e->getMessage());
        }
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        if ($quantity < 1 || ! $this->quote) {
            return;
        }

        $item = $this->quote->items()->find($itemId);
        if (! $item) {
            return;
        }

        try {
            app(QuoteService::class)->updateItemQuantity($item, $quantity);
            $this->loadQuote();
            $this->dispatch('quote-updated');
        } catch (\InvalidArgumentException $e) {
            session()->flash('floating-quote-error', $e->getMessage());
        }
    }

    public function removeItem(int $itemId): void
    {
        if (! $this->quote) {
            return;
        }

        $item = $this->quote->items()->find($itemId);
        if (! $item) {
            return;
        }

        app(QuoteService::class)->removeItem($item);
        $this->loadQuote();
        $this->dispatch('quote-updated');
    }

    public function removeGroup(string $groupKey): void
    {
        $items = $this->getGroupItems($groupKey);
        if ($items->isEmpty()) {
            return;
        }

        $service = app(QuoteService::class);
        foreach ($items as $item) {
            $service->removeItem($item);
        }
        $this->loadQuote();
        $this->dispatch('quote-updated');
    }

    public function removeFrozenGroup(int $markingGroup): void
    {
        if (! $this->quote) {
            return;
        }

        $items = $this->quote->items()->where('marking_group', $markingGroup)->get();
        $service = app(QuoteService::class);
        foreach ($items as $item) {
            $service->removeItem($item);
        }

        // Remove logos for this group
        unset($this->allGroupMarkings[(string) $markingGroup]);
        $this->quote->update(['markings' => $this->allGroupMarkings]);

        $this->loadQuote();
        $this->dispatch('quote-updated');
    }

    // ─── Logo methods ───────────────────────────────────────────

    public function addLogo(): void
    {
        // Auto-collapse all existing logos that have a technique chosen
        foreach ($this->logos as $i => &$logo) {
            if (! empty($logo['technique_id'])) {
                $logo['collapsed'] = true;
            }
        }
        unset($logo);

        $this->logos[] = [
            'type' => 'logo',
            'size' => 'A4',
            'colors' => '1',
            'zone' => '',
            'different' => false,
            'textiles' => [],
            'technique_id' => null,
            'collapsed' => false,
        ];
        $this->activeLogoIndex = count($this->logos) - 1;
        $this->persistLogos();
    }

    public function addNameMarking(): void
    {
        foreach ($this->logos as $i => &$logo) {
            if (! empty($logo['technique_id'])) {
                $logo['collapsed'] = true;
            }
        }
        unset($logo);

        $this->logos[] = [
            'type' => 'name',
            'name_size' => 'petit',  // petit or grand
            'name_lines' => '1',     // 1, 2 or 3
            'zone' => '',
            'different' => false,
            'textiles' => [],
            'technique_id' => null,
            'collapsed' => false,
        ];
        $this->activeLogoIndex = count($this->logos) - 1;
        $this->persistLogos();
    }

    public function updateNameSize(int $index, string $size): void
    {
        if (! isset($this->logos[$index]) || ! in_array($size, ['petit', 'grand'])) {
            return;
        }
        $this->logos[$index]['name_size'] = $size;
        $this->persistLogos();
    }

    public function updateNameLines(int $index, string $lines): void
    {
        if (! isset($this->logos[$index]) || ! in_array($lines, ['1', '2', '3'])) {
            return;
        }
        $this->logos[$index]['name_lines'] = $lines;
        $this->persistLogos();
    }

    public function applyNameTechnique(int $index, int $techniqueId): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }
        // Only allow broderie (1), DTF (6), flex (5)
        $allowed = \App\Models\MarkingTechnique::whereIn('slug', ['broderie', 'dtf', 'flocage-flex'])
            ->pluck('id')->toArray();
        if (! in_array($techniqueId, $allowed)) {
            return;
        }
        $this->logos[$index]['technique_id'] = $techniqueId;
        $this->logos[$index]['collapsed'] = true;
        $this->persistLogos();
        $this->recalculateMultiLogoMarking();
    }

    public function removeLogo(int $index): void
    {
        unset($this->logos[$index]);
        $this->logos = array_values($this->logos);
        $this->persistLogos();
    }

    public function updateLogoSize(int $index, string $size): void
    {
        if (! isset($this->logos[$index]) || ! in_array($size, ['A7', 'A6', 'A5', 'A4', 'A3'])) {
            return;
        }
        $this->logos[$index]['size'] = $size;
        $this->persistLogos();
    }

    public function updateLogoColors(int $index, string $colors): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }
        $valid = ['1', '2', '3', '4', '5', '6', 'quadri'];
        if (! in_array($colors, $valid)) {
            return;
        }
        $this->logos[$index]['colors'] = $colors;
        $this->persistLogos();
    }

    public function updateLogoZone(int $index, string $zone): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }
        $this->logos[$index]['zone'] = $zone;
        $this->persistLogos();
    }

    public function toggleLogoDifferent(int $index): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }
        $this->logos[$index]['different'] = ! ($this->logos[$index]['different'] ?? false);

        if ($this->logos[$index]['different'] && empty($this->logos[$index]['textiles'])) {
            foreach (array_keys($this->groupedItems) as $groupKey) {
                $this->logos[$index]['textiles'][$groupKey] = [
                    'selected' => true,
                    'zone' => $this->logos[$index]['zone'] ?? '',
                ];
            }
        }
        $this->persistLogos();
    }

    public function toggleTextileForLogo(int $index, string $groupKey): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }
        $textiles = &$this->logos[$index]['textiles'];
        if (isset($textiles[$groupKey])) {
            $textiles[$groupKey]['selected'] = ! $textiles[$groupKey]['selected'];
        } else {
            $textiles[$groupKey] = ['selected' => true, 'zone' => ''];
        }
        unset($textiles);
        $this->persistLogos();
    }

    public function updateLogoTextileZone(int $index, string $groupKey, string $zone): void
    {
        if (! isset($this->logos[$index]['textiles'][$groupKey])) {
            return;
        }
        $this->logos[$index]['textiles'][$groupKey]['zone'] = $zone;
        $this->persistLogos();
    }

    private function persistLogos(): void
    {
        if ($this->quote) {
            $activeGroup = (string) ($this->quote->active_marking_group ?? 0);
            $this->allGroupMarkings[$activeGroup] = $this->logos;
            $this->quote->update(['markings' => $this->allGroupMarkings]);
        }
    }

    /**
     * Expand a specific logo for editing (collapse all others).
     */
    public function expandLogo(int $index): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }

        foreach ($this->logos as $i => &$logo) {
            $logo['collapsed'] = ($i !== $index);
        }
        unset($logo);

        $this->activeLogoIndex = $index;
        $this->persistLogos();
    }

    /**
     * Collapse a logo (only if it has a technique assigned).
     */
    public function collapseLogo(int $index): void
    {
        if (! isset($this->logos[$index])) {
            return;
        }

        $this->logos[$index]['collapsed'] = true;
        $this->activeLogoIndex = null;
        $this->persistLogos();
    }

    /**
     * Apply a technique recommendation to a specific logo.
     */
    public function applyLogoRecommendation(int $logoIndex, string $type, ?int $altIndex = null): void
    {
        if (! isset($this->logos[$logoIndex])) {
            return;
        }

        $recs = $this->logoRecommendations($logoIndex);

        $combination = match ($type) {
            'cheapest' => $recs['cheapest'] ?? null,
            'quality' => $recs['quality'] ?? null,
            'alternative' => $recs['alternatives'][$altIndex] ?? null,
            'allOption' => $recs['allOptions'][$altIndex] ?? null,
            default => null,
        };

        if (! $combination || empty($combination['groups'])) {
            return;
        }

        // Check if this is a mix (different techniques per group)
        $techniqueIds = array_values(array_unique(array_map(fn ($g) => $g['technique']->id, $combination['groups'])));
        $isMix = count($techniqueIds) > 1;

        if ($isMix) {
            // Apply technique per textile group
            foreach ($combination['groups'] as $groupKey => $groupOpt) {
                $this->groupTechniques[$groupKey] = $groupOpt['technique']->id;
            }
            // Store first technique on logo as default display
            $firstGroup = collect($combination['groups'])->first();
            $this->logos[$logoIndex]['technique_id'] = $firstGroup['technique']->id;
            $this->logos[$logoIndex]['is_mix'] = true;
            $this->logos[$logoIndex]['mix_techniques'] = array_map(fn ($g) => $g['technique']->id, $combination['groups']);
        } else {
            $techniqueId = $techniqueIds[0];
            $this->logos[$logoIndex]['technique_id'] = $techniqueId;
            $this->logos[$logoIndex]['is_mix'] = false;
        }

        $this->logos[$logoIndex]['selected_label'] = $combination['label'] ?? null;
        $this->logos[$logoIndex]['selected_opt_index'] = $type === 'allOption' ? $altIndex : null;
        $this->selectedRecommendationTechniqueId = $this->logos[$logoIndex]['technique_id'];
        $this->persistLogos();

        // Recalculate prices with all logos' techniques summed
        $this->recalculateMultiLogoMarking();
    }

    /**
     * Get heat warning config for the current product's category.
     * Returns ['techniques' => [6,10,11], 'message' => '...'] or null.
     */
    public function getHeatWarning(): ?array
    {
        if (empty($this->groupedItems)) {
            return null;
        }

        $firstItem = collect($this->groupedItems)->flatten(1)->first();
        if (! $firstItem) {
            return null;
        }

        $product = \App\Models\Product::find($firstItem->product_id);
        if (! $product || ! $product->category) {
            return null;
        }

        $filters = $product->category->getEffectiveFilters();
        $warning = $filters['_heat_warning'] ?? null;

        if (! $warning) {
            return null;
        }

        return [
            'techniques' => $warning['heat_warning_techniques'] ?? [],
            'message' => $warning['heat_warning_message'] ?? '',
        ];
    }

    /**
     * Get recommendations for a specific logo index.
     */
    public function logoRecommendations(int $logoIndex): array
    {
        if (! isset($this->logos[$logoIndex]) || empty($this->groupedItems)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => [], 'allOptions' => []];
        }

        return app(MarkingRecommendationService::class)
            ->recommendForLogo($this->groupedItems, $this->logos[$logoIndex]);
    }

    /**
     * Recalculate marking prices summing all logos with assigned techniques.
     */
    private function recalculateMultiLogoMarking(): void
    {
        if (empty($this->groupedItems)) {
            return;
        }

        $logosWithTechniques = array_filter($this->logos, fn ($l) => ! empty($l['technique_id']));

        if (empty($logosWithTechniques)) {
            return;
        }

        $service = app(QuoteService::class);

        // Get all items in the active marking group
        foreach ($this->groupedItems as $groupKey => $group) {
            $items = $this->getGroupItems($groupKey);
            if ($items->isEmpty()) {
                continue;
            }

            // Pour les mix : remplacer le technique_id par celui spécifique au groupe
            $adjustedLogos = array_map(function ($logo) use ($groupKey) {
                if (($logo['is_mix'] ?? false) && !empty($logo['mix_techniques'])) {
                    // Chercher la technique assignée à ce groupe
                    $groupTechId = $this->groupTechniques[$groupKey] ?? null;
                    if ($groupTechId) {
                        $logo['technique_id'] = $groupTechId;
                    }
                }
                return $logo;
            }, $logosWithTechniques);

            $service->updateGroupMarkingMultiLogo($items, $adjustedLogos);
        }

        $this->loadQuote();
    }

    // ─── Legacy recommendations (kept for backward compat) ────────

    #[Computed]
    public function recommendations(): array
    {
        if (empty($this->logos) || empty($this->groupedItems)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => [], 'allOptions' => []];
        }

        // If only one logo, use it for recommendations
        $activeIdx = $this->activeLogoIndex ?? 0;
        if (isset($this->logos[$activeIdx])) {
            return app(MarkingRecommendationService::class)
                ->recommendForLogo($this->groupedItems, $this->logos[$activeIdx]);
        }

        return app(MarkingRecommendationService::class)->recommend($this->groupedItems, $this->logos);
    }

    public function applyRecommendation(string $type, ?int $altIndex = null): void
    {
        // Delegate to per-logo version
        $activeIdx = $this->activeLogoIndex ?? 0;
        $this->applyLogoRecommendation($activeIdx, $type, $altIndex);
    }

    // ─── End logo methods & recommendations ──────────────────────

    /**
     * Clear the entire quote (all items, all groups).
     */
    public function clearAll(): void
    {
        if (! $this->quote) {
            return;
        }

        $service = app(QuoteService::class);
        foreach ($this->quote->items as $item) {
            $service->removeItem($item);
        }

        $this->quote->update([
            'active_marking_group' => 0,
            'markings' => [],
        ]);

        $this->logos = [];
        $this->allGroupMarkings = [];
        $this->loadQuote();
        $this->dispatch('quote-updated');
    }

    /**
     * Unfreeze a group to make it editable again (set it as active).
     */
    public function editFrozenGroup(int $markingGroup): void
    {
        if (! $this->quote) {
            return;
        }

        $activeGroup = $this->quote->active_marking_group ?? 0;
        $hasActiveItems = $this->quote->items()->where('marking_group', $activeGroup)->exists();

        if ($hasActiveItems) {
            // Freeze current active items first
            $this->allGroupMarkings[(string) $activeGroup] = $this->logos;
            $maxGroup = (int) $this->quote->items()->max('marking_group');
            $newGroupNum = $maxGroup + 1;
            $this->quote->items()
                ->where('marking_group', $activeGroup)
                ->update(['marking_group' => $newGroupNum]);
            // Move markings too
            $this->allGroupMarkings[(string) $newGroupNum] = $this->allGroupMarkings[(string) $activeGroup] ?? [];
        }

        // Move frozen group items to active
        $this->quote->items()
            ->where('marking_group', $markingGroup)
            ->update(['marking_group' => $activeGroup]);

        // Restore frozen group logos as active
        $this->logos = $this->allGroupMarkings[(string) $markingGroup] ?? [];
        $this->allGroupMarkings[(string) $activeGroup] = $this->logos;
        unset($this->allGroupMarkings[(string) $markingGroup]);

        $this->quote->update(['markings' => $this->allGroupMarkings]);
        $this->loadQuote();
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.floating-quote');
    }
}
