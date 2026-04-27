<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ProductSize;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Populate compatible_techniques from pivot table
        if (empty($data['compatible_techniques'])) {
            $data['compatible_techniques'] = $this->record->techniqueRules()
                ->where('is_compatible', true)
                ->pluck('marking_technique_id')
                ->toArray();
        }

        // Build color_groups from existing ProductColor/ProductSize records
        $data['color_groups'] = $this->buildColorGroups();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['color_groups']);

        return $data;
    }

    protected function afterSave(): void
    {
        $colorGroups = $this->data['color_groups'] ?? [];
        CreateProduct::syncColorGroups($this->record, $colorGroups);
    }

    /**
     * Rebuild color_groups from existing DB records.
     * Groups colors that share the same set of sizes + same supplier_price + same large supplement.
     */
    private function buildColorGroups(): array
    {
        $colors = $this->record->colors()->with('sizes')->orderBy('sort_order')->get();

        if ($colors->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($colors as $color) {
            $sizeSet = $color->sizes->pluck('size')->sort()->values()->implode(',');
            $supplierPrice = $color->supplier_price ? (string) $color->supplier_price : '';

            // Get large size supplement from first large size found
            $largeSupplement = '0';
            foreach ($color->sizes as $size) {
                if (in_array($size->size, ProductSize::LARGE_SIZES) && (float) $size->price_supplement > 0) {
                    $largeSupplement = (string) $size->price_supplement;
                    break;
                }
            }

            $key = $sizeSet . '|' . $supplierPrice . '|' . $largeSupplement;
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'colors' => [],
                    'sizes' => $sizeSet !== '' ? explode(',', $sizeSet) : [],
                    'group_supplier_price' => $supplierPrice !== '' ? $supplierPrice : null,
                    'large_size_supplement' => $largeSupplement,
                ];
            }
            $grouped[$key]['colors'][] = $color->name;
        }

        return array_values($grouped);
    }
}
