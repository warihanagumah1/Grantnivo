<?php

namespace App\Livewire\Admin;

use App\Models\GrantOpportunity;
use App\Models\WaitlistEntry;
use App\Services\Analytics\AdminMetrics;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Admin overview')]
class Overview extends AdminPage
{
    public function render(): View
    {
        return view('livewire.admin.overview', [
            'stats' => app(AdminMetrics::class)->overview(),
            'opportunities' => GrantOpportunity::query()->published()->latest()->take(5)->get(),
            'entries' => WaitlistEntry::query()->latest()->take(5)->get(),
        ]);
    }
}
