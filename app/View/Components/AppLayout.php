<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}

// j'ai terminé la creation/annulation collocation et l'ajout des depences et categories,
//aujourdhui j'ai comme objectif d'ajouter l'envoi des invation et dashboard d'admin
