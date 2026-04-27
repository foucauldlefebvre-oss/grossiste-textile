<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
    ) {}

    public function query()
    {
        return Invoice::query()
            ->whereBetween('issued_at', [$this->from, $this->to . ' 23:59:59'])
            ->with(['order', 'user'])
            ->orderBy('number');
    }

    public function headings(): array
    {
        return [
            'Numero',
            'Date',
            'Commande',
            'Client',
            'Societe',
            'Total HT',
            'TVA',
            'Total TTC',
            'Acompte recu',
            'Restant du',
            'Statut',
            'Mode paiement',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->number,
            $invoice->issued_at->format('d/m/Y'),
            $invoice->order?->reference ?? '',
            $invoice->user?->name ?? '',
            $invoice->user?->company ?? '',
            number_format((float) $invoice->total_ht, 2, ',', ''),
            number_format((float) $invoice->total_tva, 2, ',', ''),
            number_format((float) $invoice->total_ttc, 2, ',', ''),
            number_format((float) $invoice->amount_paid, 2, ',', ''),
            number_format((float) $invoice->amount_remaining, 2, ',', ''),
            $invoice->status_label,
            $invoice->payment_method_label,
        ];
    }

    public function title(): string
    {
        return 'Factures';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
