<?php

namespace App\Livewire\Admin;

use App\Models\WaitlistEntry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Waitlist signups')]
class Waitlist extends AdminPage
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $search = mb_substr($this->search, 0, 200);

        return view('livewire.admin.waitlist', [
            'total' => WaitlistEntry::query()->count(),
            'entries' => WaitlistEntry::query()->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('organisation', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))->latest()->paginate(20),
        ]);
    }
}
