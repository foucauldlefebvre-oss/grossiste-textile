<?php

namespace App\Livewire;

use App\Helpers\BatHelper;
use App\Models\Quote;
use App\Services\QuotePdfService;
use App\Services\QuoteService;
use Illuminate\Support\Str;
use Livewire\Component;

class QuotePage extends Component
{
    public ?Quote $quote = null;
    public bool $submitted = false;
    public bool $showConfirmModal = false;
    public bool $quoteEdited = false;

    public function mount(): void
    {
        $this->loadQuote();

        // Si le devis est deja edite (sent), afficher la vue editee
        if ($this->quote && $this->quote->status === 'sent') {
            $this->quoteEdited = true;
        }
    }

    public function loadQuote(): void
    {
        $query = Quote::with([
            'items.product',
            'items.color',
            'items.size',
            'items.technique',
        ]);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id')->where('session_id', session()->getId());
        }

        $this->quote = $query->latest()->first();
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
            session()->flash('quote-error', $e->getMessage());
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

    public function generateBat(): void
    {
        if (! $this->quote || ! $this->quote->markings) {
            return;
        }

        if ($this->quote->bat_token) {
            $this->redirect('/bat/' . $this->quote->bat_token);

            return;
        }

        $zoneLabels = [
            'poitrine_gauche' => 'Poitrine gauche',
            'poitrine_droite' => 'Poitrine droite',
            'poitrine_centre' => 'Poitrine centré',
            'dos_centre' => 'Dos centré',
            'dos_haut' => 'Dos haut',
            'manche_gauche' => 'Manche gauche',
            'manche_droite' => 'Manche droite',
            'col' => 'Col',
        ];

        $zone = data_get($this->quote->markings, '0.zone', '');
        $format = data_get($this->quote->markings, '0.size', 'A7');
        $firstItem = $this->quote->items->first();
        $productName = $firstItem?->product?->name ?? '';

        $this->quote->update([
            'bat_token' => Str::random(48),
            'bat_status' => 'sent',
            'bat_sent_at' => now(),
            'bat_format' => $format,
            'bat_position_label' => $zoneLabels[$zone] ?? 'Non défini',
            'bat_svg_template' => BatHelper::buildTemplatesJson($this->quote),
        ]);

        $this->redirect('/bat/' . $this->quote->bat_token);
    }

    // ─── Edition du devis PDF ─────────────────────────────────────

    public function confirmEditQuote(): void
    {
        $this->showConfirmModal = true;
    }

    public function cancelEditQuote(): void
    {
        $this->showConfirmModal = false;
    }

    public function editQuotePdf(): void
    {
        if (! $this->quote) {
            return;
        }

        $this->showConfirmModal = false;

        // Passer le devis en "sent" avec expiration 30 jours
        if ($this->quote->status === 'draft') {
            $this->quote->update([
                'status' => 'sent',
                'expires_at' => now()->addDays(30),
            ]);
        }

        // Generer le PDF
        app(QuotePdfService::class)->generate($this->quote);

        $this->quoteEdited = true;
        $this->loadQuote();
        $this->dispatch('quote-updated');

        // Telecharger automatiquement via JS
        $this->dispatch('download-quote-pdf', url: asset('storage/' . $this->quote->bat_pdf));
    }

    // ─── Fin edition devis ────────────────────────────────────────

    public function submitQuote(): void
    {
        if (! $this->quote) {
            return;
        }

        try {
            app(QuoteService::class)->submit($this->quote);
            $this->submitted = true;
            $this->dispatch('quote-updated');
        } catch (\InvalidArgumentException $e) {
            session()->flash('quote-error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.quote-page');
    }
}
