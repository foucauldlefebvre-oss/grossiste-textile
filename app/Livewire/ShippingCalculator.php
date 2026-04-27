<?php

namespace App\Livewire;

use App\Models\Quote;
use App\Services\ShippingService;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class ShippingCalculator extends Component
{
    public string $country = 'FR';
    public string $postalCode = '';
    public ?int $selectedRateId = null;

    public Collection $rates;
    public float $estimatedWeight = 0;
    public string $zone = '';
    public bool $calculated = false;

    public function mount(): void
    {
        $this->rates = collect();
    }

    #[On('quote-updated')]
    public function onQuoteUpdated(): void
    {
        if ($this->calculated) {
            $this->calculate();
        }
    }

    public function calculate(): void
    {
        $service = app(ShippingService::class);

        // Detect zone
        $this->zone = $service->detectZone($this->country, $this->postalCode);

        // Get current draft quote for weight estimation
        $quote = $this->getDraftQuote();
        if ($quote) {
            $quote->load('items.product');
            $this->estimatedWeight = $service->estimateWeight($quote->items);
        } else {
            $this->estimatedWeight = 0.3; // default
        }

        // Get rates
        $this->rates = $service->calculateRates($this->estimatedWeight, $this->zone);
        $this->calculated = true;

        // Auto-select cheapest
        if ($this->rates->isNotEmpty() && ! $this->selectedRateId) {
            $this->selectedRateId = $this->rates->first()['id'];
        }
    }

    public function selectRate(int $rateId): void
    {
        $this->selectedRateId = $rateId;
    }

    private function getDraftQuote(): ?Quote
    {
        $query = Quote::draft();

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id')->where('session_id', session()->getId());
        }

        return $query->first();
    }

    public function render()
    {
        return view('livewire.shipping-calculator');
    }
}
