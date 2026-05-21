<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public string $coverImage;
    /**
     * Get the view / contents that represents the component.
     */

    public function __construct(string $coverImage = null)
    {
        $this->coverImage = $coverImage
            ?? asset('assets/admin/theme/assets/images/auth/login1.png');
    }

    public function render(): View
    {
        return view('layouts.guest');
    }
}
