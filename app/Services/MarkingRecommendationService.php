<?php

namespace App\Services;

use App\Models\MarkingTechnique;

class MarkingRecommendationService
{
    public function __construct(
        private QuoteService $quoteService,
    ) {}

    /**
     * Generate marking technique recommendations for the current quote groups.
     *
     * @param  array $groups  From FloatingQuote::groupedItems (product, color, totalQuantity, compatibleTechniques)
     * @param  array $logos   Logo configurations (size, colors, zone, different, textiles)
     * @return array          ['cheapest' => [...], 'quality' => [...], 'alternatives' => [...]]
     */
    public function recommend(array $groups, array $logos): array
    {
        if (empty($groups) || empty($logos)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => []];
        }

        $logo = $logos[0];
        $numColors = $this->resolveColorCount($logo['colors'] ?? '1');
        $visualFormat = $logo['size'] ?? 'A4';
        $isQuadri = ($logo['colors'] ?? '1') === 'quadri';

        // Quantité totale de tous les groupes (pour le min_quantity)
        $grandTotal = array_sum(array_map(fn ($g) => $g['totalQuantity'], $groups));

        // For each group, find eligible techniques with their costs
        $groupOptions = [];
        foreach ($groups as $groupKey => $group) {
            $eligible = $this->getEligibleTechniques($group, $numColors, $isQuadri, $visualFormat, $grandTotal);
            if (empty($eligible)) {
                continue;
            }

            $options = [];
            $productId = $group['product']->id;
            foreach ($eligible as $technique) {
                $textileColorName = $group['color']?->name;
                $unitPrice = $this->quoteService->calculateMarkingPrice(
                    $technique->id,
                    $group['totalQuantity'],
                    min($numColors, $technique->constraint?->max_colors ?? $numColors),
                    $textileColorName,
                    $visualFormat,
                );
                $unitPrice *= $this->quoteService->getProductTechniqueMultiplier($productId, $technique->id);
                $setupCost = $this->quoteService->getSetupCost(
                    $technique->id,
                    $group['totalQuantity'],
                    min($numColors, $technique->constraint?->max_colors ?? $numColors),
                );
                $groupCost = round(($unitPrice * $group['totalQuantity']) + $setupCost, 2);

                $options[] = [
                    'technique' => $technique,
                    'unitPrice' => $unitPrice,
                    'setupCost' => $setupCost,
                    'groupCost' => $groupCost,
                    'qualityRating' => (float) ($technique->quality_rating ?? 0),
                ];
            }

            $groupOptions[$groupKey] = $options;
        }

        if (empty($groupOptions)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => []];
        }

        $cheapest = $this->buildCombination($groupOptions, $groups, 'cheapest');
        $quality = $this->buildCombination($groupOptions, $groups, 'quality');
        $alternatives = $this->buildAlternatives($groupOptions, $groups, $cheapest, $quality);
        $allOptions = $this->buildAllOptions($groupOptions, $groups);

        return [
            'cheapest' => $cheapest,
            'quality' => $quality,
            'alternatives' => $alternatives,
            'allOptions' => $allOptions,
        ];
    }

    /**
     * Filter compatible techniques by constraints.
     */
    /** Format size order: smaller index = smaller format */
    private const FORMAT_ORDER = ['A7' => 1, 'A6' => 2, 'A5' => 3, 'A4' => 4, 'A3' => 5];

    private function getEligibleTechniques(array $group, int $numColors, bool $isQuadri, string $format = 'A4', ?int $grandTotalQuantity = null): array
    {
        $compatible = $group['compatibleTechniques'] ?? collect();
        $eligible = [];
        $requestedSize = self::FORMAT_ORDER[$format] ?? 4;
        // Utiliser la quantité totale du marquage (tous groupes) pour le min_quantity
        $qtyForMinCheck = $grandTotalQuantity ?? $group['totalQuantity'];

        foreach ($compatible as $technique) {
            if (! $technique->is_active) {
                continue;
            }

            $constraint = $technique->constraint;

            // Min quantity check — basé sur la quantité TOTALE du marquage
            if ($constraint && $constraint->min_quantity && $qtyForMinCheck < $constraint->min_quantity) {
                continue;
            }

            // Max colors check
            if ($constraint && $constraint->max_colors !== null) {
                if ($isQuadri) {
                    // Quadri requires max_colors = null (unlimited)
                    continue;
                }
                if ($numColors > $constraint->max_colors) {
                    continue;
                }
            }

            // Max format check (e.g. badge PVC max A6, requested A4 → skip)
            if ($constraint && $constraint->max_format) {
                $maxSize = self::FORMAT_ORDER[$constraint->max_format] ?? 5;
                if ($requestedSize > $maxSize) {
                    continue;
                }
            }

            $eligible[] = $technique;
        }

        return $eligible;
    }

    /**
     * Build a combination (cheapest or quality) by picking the best technique per group.
     */
    private function buildCombination(array $groupOptions, array $groups, string $strategy): ?array
    {
        // 1. Chercher les techniques communes à TOUS les groupes
        $commonTechniqueIds = null;
        foreach ($groupOptions as $options) {
            $ids = array_map(fn ($o) => $o['technique']->id, $options);
            $commonTechniqueIds = $commonTechniqueIds === null
                ? array_flip($ids)
                : array_intersect_key($commonTechniqueIds, array_flip($ids));
        }

        // 2. Si des techniques communes existent, choisir la meilleure parmi celles-ci
        if (! empty($commonTechniqueIds)) {
            $bestCommon = null;
            $bestCost = null;
            $bestQuality = null;

            foreach (array_keys($commonTechniqueIds) as $techniqueId) {
                $totalCost = 0;
                $comboGroups = [];
                $quality = 0;

                foreach ($groupOptions as $groupKey => $options) {
                    $match = null;
                    foreach ($options as $opt) {
                        if ($opt['technique']->id === $techniqueId) {
                            $match = $opt;
                            break;
                        }
                    }
                    if (! $match) {
                        continue 2;
                    }
                    $comboGroups[$groupKey] = $match;
                    $totalCost += $match['groupCost'];
                    $quality = $match['qualityRating'];
                }

                $isBetter = false;
                if ($strategy === 'cheapest') {
                    $isBetter = $bestCost === null || $totalCost < $bestCost;
                } else {
                    $isBetter = $bestQuality === null || $quality > $bestQuality
                        || ($quality === $bestQuality && ($bestCost === null || $totalCost > $bestCost));
                }

                if ($isBetter) {
                    $bestCommon = $comboGroups;
                    $bestCost = $totalCost;
                    $bestQuality = $quality;
                }
            }

            // Pour cheapest, la technique commune est toujours optimale
            if ($bestCommon && $strategy === 'cheapest') {
                $technique = collect($bestCommon)->first()['technique'];
                return [
                    'label' => $technique->name,
                    'totalCost' => round($bestCost, 2),
                    'groups' => $bestCommon,
                ];
            }
        }

        // 3. Mix : meilleure technique PAR GROUPE (toujours calculé pour quality)
        $combination = [];
        $totalCost = 0;
        $techniqueNames = [];

        foreach ($groupOptions as $groupKey => $options) {
            if (empty($options)) {
                continue;
            }

            if ($strategy === 'cheapest') {
                usort($options, fn ($a, $b) => $a['groupCost'] <=> $b['groupCost']);
            } else {
                usort($options, function ($a, $b) {
                    $ratingCmp = $b['qualityRating'] <=> $a['qualityRating'];
                    return $ratingCmp !== 0 ? $ratingCmp : $b['groupCost'] <=> $a['groupCost'];
                });
            }

            $best = $options[0];
            $combination[$groupKey] = $best;
            $totalCost += $best['groupCost'];
            $techniqueNames[$best['technique']->id] = $best['technique']->name;
        }

        if (empty($combination)) {
            // Retourner le meilleur commun si on en a un
            if ($bestCommon) {
                $technique = collect($bestCommon)->first()['technique'];
                return [
                    'label' => $technique->name,
                    'totalCost' => round($bestCost, 2),
                    'groups' => $bestCommon,
                ];
            }
            return null;
        }

        $uniqueNames = array_unique(array_values($techniqueNames));
        $label = count($uniqueNames) === 1 ? $uniqueNames[0] : 'Mixte (' . implode(' + ', $uniqueNames) . ')';

        $mixResult = [
            'label' => $label,
            'totalCost' => round($totalCost, 2),
            'groups' => $combination,
        ];

        // Pour quality : comparer mix vs meilleur commun, garder le plus quali
        if ($strategy === 'quality' && $bestCommon) {
            $mixMaxQuality = collect($combination)->max('qualityRating');
            if ($bestQuality >= $mixMaxQuality) {
                // Le commun est aussi bon ou meilleur
                $technique = collect($bestCommon)->first()['technique'];
                return [
                    'label' => $technique->name,
                    'totalCost' => round($bestCost, 2),
                    'groups' => $bestCommon,
                ];
            }
        }

        return $mixResult;
    }

    /**
     * Build alternative single-technique combinations (same technique for all groups).
     */
    private function buildAlternatives(array $groupOptions, array $groups, ?array $cheapest, ?array $quality): array
    {
        // Collect all technique IDs used in cheapest/quality
        $usedIds = [];
        if ($cheapest) {
            foreach ($cheapest['groups'] as $opt) {
                $usedIds[$opt['technique']->id] = true;
            }
        }
        if ($quality) {
            foreach ($quality['groups'] as $opt) {
                $usedIds[$opt['technique']->id] = true;
            }
        }

        // Find techniques eligible in ALL groups
        $commonTechniqueIds = null;
        foreach ($groupOptions as $options) {
            $ids = array_map(fn ($o) => $o['technique']->id, $options);
            $commonTechniqueIds = $commonTechniqueIds === null
                ? array_flip($ids)
                : array_intersect_key($commonTechniqueIds, array_flip($ids));
        }

        if (empty($commonTechniqueIds)) {
            return [];
        }

        $alternatives = [];

        foreach (array_keys($commonTechniqueIds) as $techniqueId) {
            $totalCost = 0;
            $altGroups = [];
            $techniqueName = null;

            foreach ($groupOptions as $groupKey => $options) {
                $match = null;
                foreach ($options as $opt) {
                    if ($opt['technique']->id === $techniqueId) {
                        $match = $opt;
                        break;
                    }
                }
                if (! $match) {
                    continue 2; // technique not available for this group
                }

                $altGroups[$groupKey] = $match;
                $totalCost += $match['groupCost'];
                $techniqueName = $match['technique']->name;
            }

            // Skip if this exact combination matches cheapest or quality
            if ($cheapest && $this->isSameCombination($altGroups, $cheapest['groups'])) {
                continue;
            }
            if ($quality && $this->isSameCombination($altGroups, $quality['groups'])) {
                continue;
            }

            $alternatives[] = [
                'label' => $techniqueName,
                'totalCost' => round($totalCost, 2),
                'groups' => $altGroups,
            ];
        }

        // Sort alternatives by total cost
        usort($alternatives, fn ($a, $b) => $a['totalCost'] <=> $b['totalCost']);

        return $alternatives;
    }

    /**
     * Build ALL options for display: common techniques + mix combinations.
     */
    private function buildAllOptions(array $groupOptions, array $groups): array
    {
        $allOptions = [];
        $addedSignatures = [];

        // 1. Single-technique options (same technique for all groups)
        $commonTechniqueIds = null;
        foreach ($groupOptions as $options) {
            $ids = array_map(fn ($o) => $o['technique']->id, $options);
            $commonTechniqueIds = $commonTechniqueIds === null
                ? array_flip($ids)
                : array_intersect_key($commonTechniqueIds, array_flip($ids));
        }

        foreach (array_keys($commonTechniqueIds ?? []) as $techniqueId) {
            $totalCost = 0;
            $optGroups = [];
            $technique = null;

            foreach ($groupOptions as $groupKey => $options) {
                $match = null;
                foreach ($options as $opt) {
                    if ($opt['technique']->id === $techniqueId) {
                        $match = $opt;
                        break;
                    }
                }
                if (! $match) {
                    continue 2;
                }

                $optGroups[$groupKey] = $match;
                $totalCost += $match['groupCost'];
                $technique = $match['technique'];
            }

            $sig = $this->combinationSignature($optGroups);
            $addedSignatures[$sig] = true;

            $allOptions[] = [
                'label' => $technique->name,
                'techniqueId' => $technique->id,
                'qualityRating' => (float) ($technique->quality_rating ?? 0),
                'totalCost' => round($totalCost, 2),
                'groups' => $optGroups,
            ];
        }

        // 2. Mix combinations: cheapest per group and quality per group
        foreach (['cheapest', 'quality'] as $strategy) {
            $mix = $this->buildCombination($groupOptions, $groups, $strategy);
            if (! $mix) {
                continue;
            }

            $sig = $this->combinationSignature($mix['groups']);
            if (isset($addedSignatures[$sig])) {
                continue; // already in the list as a single-technique option
            }
            $addedSignatures[$sig] = true;

            // Determine label and average quality
            $techNames = [];
            $totalQuality = 0;
            $count = 0;
            foreach ($mix['groups'] as $opt) {
                $techNames[$opt['technique']->id] = $opt['technique']->name;
                $totalQuality += $opt['qualityRating'];
                $count++;
            }

            $uniqueNames = array_unique(array_values($techNames));
            $label = count($uniqueNames) === 1
                ? $uniqueNames[0]
                : 'Mixte (' . implode(' + ', $uniqueNames) . ')';

            // Use the first technique ID for selection (the system applies per-group anyway)
            $firstTechId = array_values($mix['groups'])[0]['technique']->id;

            $allOptions[] = [
                'label' => $label,
                'techniqueId' => $firstTechId,
                'qualityRating' => $count > 0 ? round($totalQuality / $count, 1) : 0,
                'totalCost' => $mix['totalCost'],
                'groups' => $mix['groups'],
                'isMix' => true,
            ];
        }

        // Sort: cheapest first, then best quality second, then rest by cost
        usort($allOptions, function ($a, $b) {
            return $a['totalCost'] <=> $b['totalCost'];
        });

        if (count($allOptions) > 2) {
            // Find the best quality option (not already first)
            $bestQualityIdx = null;
            $bestQualityRating = -1;
            foreach ($allOptions as $idx => $opt) {
                if ($idx === 0) {
                    continue; // skip cheapest
                }
                if ($opt['qualityRating'] > $bestQualityRating) {
                    $bestQualityRating = $opt['qualityRating'];
                    $bestQualityIdx = $idx;
                }
            }

            // Move best quality to position 1 (second)
            if ($bestQualityIdx !== null && $bestQualityIdx !== 1) {
                $bestQuality = $allOptions[$bestQualityIdx];
                array_splice($allOptions, $bestQualityIdx, 1);
                array_splice($allOptions, 1, 0, [$bestQuality]);
            }
        }

        return $allOptions;
    }

    /**
     * Generate a unique signature for a combination (technique IDs per group key).
     */
    private function combinationSignature(array $groups): string
    {
        $parts = [];
        foreach ($groups as $groupKey => $opt) {
            $parts[] = $groupKey . ':' . $opt['technique']->id;
        }
        sort($parts);
        return implode('|', $parts);
    }

    /**
     * Check if two group combinations use the same techniques.
     */
    private function isSameCombination(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }
        foreach ($a as $groupKey => $opt) {
            if (! isset($b[$groupKey]) || $opt['technique']->id !== $b[$groupKey]['technique']->id) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generate recommendations for a SINGLE logo across all textile groups.
     */
    public function recommendForLogo(array $groups, array $logo): array
    {
        if (empty($groups)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => [], 'allOptions' => []];
        }

        $numColors = $this->resolveColorCount($logo['colors'] ?? '1');
        $visualFormat = $logo['size'] ?? 'A4';
        $isQuadri = ($logo['colors'] ?? '1') === 'quadri';

        // Check if any group has a dark color → force dark pricing for sérigraphie
        $hasDarkTextile = false;
        foreach ($groups as $group) {
            $colorName = $group['color']?->name;
            if ($colorName && ! \App\Models\SerigraphiePricing::isLightColor($colorName)) {
                $hasDarkTextile = true;
                break;
            }
        }

        $grandTotal = array_sum(array_map(fn ($g) => $g['totalQuantity'], $groups));

        $groupOptions = [];
        foreach ($groups as $groupKey => $group) {
            $eligible = $this->getEligibleTechniques($group, $numColors, $isQuadri, $visualFormat, $grandTotal);
            if (empty($eligible)) {
                continue;
            }

            $options = [];
            $productId = $group['product']->id;
            foreach ($eligible as $technique) {
                // For sérigraphie: if any textile is dark, force dark pricing for all
                $textileColorName = $group['color']?->name;
                if ($hasDarkTextile && $technique->slug === 'serigraphie') {
                    $textileColorName = 'noir'; // force dark pricing
                }
                $unitPrice = $this->quoteService->calculateMarkingPrice(
                    $technique->id,
                    $group['totalQuantity'],
                    min($numColors, $technique->constraint?->max_colors ?? $numColors),
                    $textileColorName,
                    $visualFormat,
                );
                $unitPrice *= $this->quoteService->getProductTechniqueMultiplier($productId, $technique->id);
                $setupCost = $this->quoteService->getSetupCost(
                    $technique->id,
                    $group['totalQuantity'],
                    min($numColors, $technique->constraint?->max_colors ?? $numColors),
                );
                $groupCost = round(($unitPrice * $group['totalQuantity']) + $setupCost, 2);

                $options[] = [
                    'technique' => $technique,
                    'unitPrice' => $unitPrice,
                    'setupCost' => $setupCost,
                    'groupCost' => $groupCost,
                    'qualityRating' => (float) ($technique->quality_rating ?? 0),
                ];
            }

            $groupOptions[$groupKey] = $options;
        }

        if (empty($groupOptions)) {
            return ['cheapest' => null, 'quality' => null, 'alternatives' => [], 'allOptions' => []];
        }

        return [
            'cheapest' => $this->buildCombination($groupOptions, $groups, 'cheapest'),
            'quality' => $this->buildCombination($groupOptions, $groups, 'quality'),
            'alternatives' => $this->buildAlternatives(
                $groupOptions, $groups,
                $this->buildCombination($groupOptions, $groups, 'cheapest'),
                $this->buildCombination($groupOptions, $groups, 'quality'),
            ),
            'allOptions' => $this->buildAllOptions($groupOptions, $groups),
        ];
    }

    /**
     * Resolve color count from logo config.
     */
    private function resolveColorCount(string $colors): int
    {
        if ($colors === 'quadri') {
            return 99;
        }

        return max(1, (int) $colors);
    }
}
