<?php

namespace App\Livewire;

use App\Models\Quote;
use Livewire\Attributes\On;
use Livewire\Component;

class QuoteCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->loadCount();
    }

    #[On('item-added-to-quote')]
    #[On('quote-updated')]
    public function loadCount(): void
    {
        $quote = $this->getDraftQuote();
        $this->count = $quote ? $quote->items()->distinct()->count(\DB::raw('CONCAT(product_id, "-", COALESCE(product_color_id, 0))')) : 0;
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
        return view('livewire.quote-counter');
    }
}
