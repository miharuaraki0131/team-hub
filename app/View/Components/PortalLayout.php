<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PortalLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public bool $showHero = false,
        public bool $showHeroButtons = false,
        public ?string $heroTitle = null,
        public ?string $heroSubtitle = null,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.portal-layout');
    }
}
