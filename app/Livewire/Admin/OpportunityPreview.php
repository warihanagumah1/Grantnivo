<?php

namespace App\Livewire\Admin;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;

#[Layout('layouts.marketing')]
class OpportunityPreview extends AdminPage
{
    public GrantOpportunity $grantOpportunity;

    public function mount(GrantOpportunity $grantOpportunity): void
    {
        abort_if($grantOpportunity->trashed() || $grantOpportunity->status->value === 'archived', 404);
        $this->grantOpportunity = $grantOpportunity;
    }

    public function render(): View
    {
        return view('livewire.marketing.opportunity-detail', ['links' => [], 'preview' => true])
            ->title('Preview: '.$this->grantOpportunity->title);
    }
}
