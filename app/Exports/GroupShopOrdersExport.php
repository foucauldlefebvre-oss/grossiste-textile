<?php

namespace App\Exports;

use App\Models\GroupShop;
use App\Models\GroupShopOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GroupShopOrdersExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $groupShopId,
    ) {}

    public function query()
    {
        return GroupShopOrder::query()
            ->where('group_shop_id', $this->groupShopId)
            ->with(['groupShopProduct.product', 'groupShopProduct.technique'])
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Prenom',
            'Email',
            'Service',
            'Produit',
            'Reference',
            'Technique',
            'Taille',
            'Quantite',
            'Prix unitaire HT',
            'Total HT',
            'Statut paiement',
            'Date commande',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->last_name,
            $order->first_name,
            $order->email,
            $order->department ?? '',
            $order->groupShopProduct?->product?->name ?? '',
            $order->groupShopProduct?->product?->reference ?? '',
            $order->groupShopProduct?->technique?->name ?? '',
            $order->size,
            $order->quantity,
            number_format((float) $order->unit_price, 2, ',', ''),
            number_format((float) $order->total, 2, ',', ''),
            match ($order->payment_status) {
                'paid' => 'Paye',
                'refunded' => 'Rembourse',
                default => 'En attente',
            },
            $order->created_at->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        $shop = GroupShop::find($this->groupShopId);

        return $shop ? substr($shop->name, 0, 31) : 'Commandes';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
