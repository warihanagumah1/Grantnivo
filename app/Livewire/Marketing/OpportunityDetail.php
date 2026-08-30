<?php

namespace App\Livewire\Marketing;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.marketing')]
class OpportunityDetail extends Component
{
    public GrantOpportunity $grantOpportunity;

    public function mount(GrantOpportunity $grantOpportunity): void
    {
        abort_unless($grantOpportunity->isPublic(), 404);
        $this->grantOpportunity = $grantOpportunity;
    }

    public function render(): View
    {
        return view('livewire.marketing.opportunity-detail')
            ->title($this->grantOpportunity->title);
    }
}
