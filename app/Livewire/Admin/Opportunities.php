<?php

namespace App\Livewire\Admin;

use App\Actions\Grants\ArchiveOpportunity;
use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Manage opportunities')]
class Opportunities extends AdminPage
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function updated(): void
    {
        $this->resetPage();
    }

    public function archive(int $id, ArchiveOpportunity $archive): void
    {
        $archive->handle(auth()->user(), GrantOpportunity::query()->findOrFail($id));
        session()->flash('success', 'Opportunity removed from the public site. You can restore it from Archived.');
    }

    public function restore(int $id, ArchiveOpportunity $archive): void
    {
        $opportunity = GrantOpportunity::withTrashed()->where(fn ($query) => $query->whereNotNull('deleted_at')->orWhere('status', 'archived'))->findOrFail($id);
        $archive->handle(auth()->user(), $opportunity, true);
        session()->flash('success', 'Opportunity restored.');
    }

    public function render(): View
    {
        $query = GrantOpportunity::query()->when($this->search !== '', fn ($query) => $query->search(mb_substr($this->search, 0, 200)));
        match ($this->status) {
            'archived' => $query->withTrashed()->where(fn ($query) => $query->whereNotNull('deleted_at')->orWhere('status', 'archived')),
            'active' => $query->open(),
            'closed' => $query->closed(),
            'draft' => $query->where('status', 'draft'),
            default => $query->where('status', '!=', 'archived'),
        };

        return view('livewire.admin.opportunities', ['opportunities' => $query->latest()->paginate(12), 'liveCount' => GrantOpportunity::query()->published()->count()]);
    }
}
