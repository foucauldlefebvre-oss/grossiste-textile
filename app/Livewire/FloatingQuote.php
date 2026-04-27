<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * TODO 2b: composant à reconstruire en panier B2B simple (Q5).
 * L'ancienne version (~970 lignes) gérait le configurateur de marquage,
 * les groupes de logos, les BAT, la sélection technique. Tout supprimé en 2a.
 *
 * Stub minimal pour ne pas casser le layout app.blade.php qui référence
 * <livewire:floating-quote />. Affiche rien tant que 2b n'est pas faite.
 */
class FloatingQuote extends Component
{
    public function render()
    {
        return view('livewire.floating-quote');
    }
}
