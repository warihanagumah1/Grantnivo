<?php

namespace App\Livewire\Admin;

use App\Services\Analytics\AdminMetrics;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Engagement & clicks')]
class Analytics extends AdminPage
{
    use WithPagination;

    public function render(): View
    {
        $metrics = app(AdminMetrics::class);

        return view('livewire.admin.analytics', ['stats' => $metrics->engagement(), 'opportunities' => $metrics->opportunities()->orderByDesc('views_count')->paginate(20)]);
    }
}
