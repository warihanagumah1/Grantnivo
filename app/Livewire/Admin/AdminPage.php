<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

abstract class AdminPage extends Component
{
    public function boot(): void
    {
        // Livewire requests must recheck staff access, not just the initial page request.
        Gate::authorize('access-admin');
    }
}
