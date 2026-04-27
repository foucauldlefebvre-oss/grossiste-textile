<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ProductColor;
use App\Models\ProductSize;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['color_groups']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $colorGroups = $this->data['color_groups'] ?? [];
        self::syncColorGroups($this->record, $colorGroups);
    }

    public static function syncColorGroups($product, array $groups): void
    {
        $allColorNames = collect($groups)->flatMap(fn ($g) => $g['colors'] ?? [])->unique()->values();

        // Remove colors no longer present
        $product->colors()
            ->whereNotIn('name', $allColorNames)
            ->each(function ($color) {
                $color->sizes()->delete();
                $color->delete();
            });

        $sortOrder = 0;

        foreach ($groups as $group) {
            $colors = $group['colors'] ?? [];
            $sizes = $group['sizes'] ?? [];
            $groupSupplierPrice = ! empty($group['group_supplier_price']) ? (float) $group['group_supplier_price'] : null;
            $largeSizeSupplement = (float) ($group['large_size_supplement'] ?? 0);

            foreach ($colors as $colorName) {
                // Find hex code from existing colors in DB
                $existingColor = ProductColor::where('name', $colorName)->first();
                $hexCode = $existingColor?->hex_code;

                $productColor = $product->colors()->updateOrCreate(
                    ['name' => $colorName],
                    [
                        'hex_code' => $hexCode ?? $product->colors()->where('name', $colorName)->value('hex_code'),
                        'supplier_price' => $groupSupplierPrice,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );

                // Sync sizes
                $existingSizes = $productColor->sizes->pluck('size')->toArray();

                foreach ($sizes as $size) {
                    $supplement = in_array($size, ProductSize::LARGE_SIZES) ? $largeSizeSupplement : 0;

                    if (in_array($size, $existingSizes)) {
                        // Update supplement if changed
                        $productColor->sizes()
                            ->where('size', $size)
                            ->update(['price_supplement' => $supplement]);
                    } else {
                        $productColor->sizes()->create([
                            'size' => $size,
                            'price_supplement' => $supplement,
                            'stock' => 0,
                            'is_available' => true,
                        ]);
                    }
                }

                // Remove deselected sizes
                $productColor->sizes()
                    ->whereNotIn('size', $sizes)
                    ->delete();
            }
        }
    }
}
