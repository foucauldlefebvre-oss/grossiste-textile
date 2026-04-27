<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class SearchBar extends Component
{
    public string $query = '';
    public bool $open = false;

    public function updatedQuery(): void
    {
        $this->open = strlen($this->query) >= 2;
    }

    public function close(): void
    {
        $this->open = false;
        $this->query = '';
    }

    public function getResultsProperty()
    {
        if (strlen($this->query) < 2) {
            return collect();
        }

        // Separer les mots de la recherche
        $words = array_filter(explode(' ', trim($this->query)), fn ($w) => strlen($w) >= 2);

        if (empty($words)) {
            return collect();
        }

        try {
            return Product::search($this->query)
                ->take(8)
                ->get()
                ->load('colors');
        } catch (\Exception $e) {
            // Fallback recherche SQL multi-champs multi-mots
            return Product::active()
                ->where(function ($q) use ($words) {
                    foreach ($words as $word) {
                        $q->where(function ($sub) use ($word) {
                            $sub->where('name', 'like', "%{$word}%")
                                ->orWhere('reference', 'like', "%{$word}%")
                                ->orWhere('description', 'like', "%{$word}%")
                                ->orWhere('short_description', 'like', "%{$word}%")
                                ->orWhere('material', 'like', "%{$word}%")
                                ->orWhere('supplier', 'like', "%{$word}%")
                                ->orWhereHas('colors', fn ($c) => $c->where('name', 'like', "%{$word}%"));
                        });
                    }
                })
                ->with('colors')
                ->limit(8)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.search-bar');
    }
}
