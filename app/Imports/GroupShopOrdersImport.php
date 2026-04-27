<?php

namespace App\Imports;

use App\Models\GroupShopOrder;
use App\Models\GroupShopProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GroupShopOrdersImport implements ToModel, WithHeadingRow, WithValidation
{
    private int $imported = 0;

    public function __construct(
        private readonly int $groupShopId,
    ) {}

    public function model(array $row): ?GroupShopOrder
    {
        $gsp = GroupShopProduct::where('group_shop_id', $this->groupShopId)
            ->whereHas('product', fn ($q) => $q->where('reference', $row['reference']))
            ->first();

        if (! $gsp) {
            return null;
        }

        $unitPrice = $gsp->fixed_price
            ? (float) $gsp->fixed_price
            : (float) $gsp->product->base_price;
        $quantity = (int) $row['quantite'];

        $this->imported++;

        return new GroupShopOrder([
            'group_shop_id' => $this->groupShopId,
            'group_shop_product_id' => $gsp->id,
            'first_name' => $row['prenom'],
            'last_name' => $row['nom'],
            'email' => $row['email'],
            'department' => $row['service'] ?? null,
            'size' => $row['taille'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($unitPrice * $quantity, 2),
        ]);
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'reference' => 'required|string',
            'taille' => 'required|string',
            'quantite' => 'required|integer|min:1',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }
}
