<?php

namespace App\Livewire\Marketing;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.marketing')]
#[Title('Grant opportunities')]
class OpportunityLibrary extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'open';

    #[Url]
    public string $type = 'all';

    /** @var array<int, string> */
    #[Url(as: 'sectors')]
    public array $selectedSectors = [];

    /** @var array<int, string> */
    #[Url(as: 'countries')]
    public array $selectedCountries = [];

    #[Url]
    public string $sort = 'deadline';

    /** @var array<int, string> */
    public array $sectors = [];

    /** @var array<int, string> */
    public array $countries = [];

    public function mount(): void
    {
        $filterable = GrantOpportunity::query()->published();
        $this->sectors = (clone $filterable)->pluck('thematic_sectors')->flatten()->filter()->unique()->sort()->values()->map(static fn (mixed $sector): string => (string) $sector)->all();
        $this->countries = (clone $filterable)->pluck('eligible_countries')->flatten()->filter()->unique()->sort()->values()->map(static fn (mixed $country): string => (string) $country)->all();

        if ($this->selectedSectors === []) {
            $this->selectedSectors = $this->sectors;
        }

        if ($this->selectedCountries === []) {
            $this->selectedCountries = $this->countries;
        }
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function selectAllSectors(): void
    {
        $this->selectedSectors = $this->sectors;
    }

    public function clearSectors(): void
    {
        $this->selectedSectors = [];
    }

    public function selectAllCountries(): void
    {
        $this->selectedCountries = $this->countries;
    }

    public function clearCountries(): void
    {
        $this->selectedCountries = [];
    }

    public function render(): View
    {
        $query = GrantOpportunity::query()->published()
            ->when($this->status === 'open', fn ($query) => $query->open())
            ->when($this->status === 'closed', fn ($query) => $query->closed())
            ->when($this->status === 'all', fn ($query) => $query->whereNotIn('status', ['draft', 'archived']))
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when($this->type !== 'all', fn ($query) => $query->where('funding_type', $this->type === 'grant' ? 'Grant' : 'Partnership'))
            ->when(count($this->selectedSectors) !== count($this->sectors), function ($query): void {
                if ($this->selectedSectors === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(function ($query): void {
                    foreach ($this->selectedSectors as $sector) {
                        $query->orWhereJsonContains('thematic_sectors', $sector);
                    }
                });
            })
            ->when(count($this->selectedCountries) !== count($this->countries), function ($query): void {
                if ($this->selectedCountries === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(function ($query): void {
                    foreach ($this->selectedCountries as $country) {
                        $query->orWhereJsonContains('eligible_countries', $country);
                    }
                });
            });

        match ($this->sort) {
            'amount-desc' => $query->orderByDesc('maximum_award'),
            'amount-asc' => $query->orderBy('maximum_award'),
            'az' => $query->orderBy('title'),
            default => $query->orderByRaw('deadline is null')->orderBy('deadline'),
        };

        return view('livewire.marketing.opportunity-library', [
            'opportunities' => $query->paginate(9),
        ]);
    }
}
