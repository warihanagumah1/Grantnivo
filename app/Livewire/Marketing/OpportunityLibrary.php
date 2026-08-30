<?php

namespace App\Livewire\Marketing;

use App\Models\GrantOpportunity;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
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
    #[Locked]
    public array $sectors = [];

    /** @var array<int, string> */
    #[Locked]
    public array $countries = [];

    public function mount(): void
    {
        $filterable = GrantOpportunity::query()->available();
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
        $this->resetPage();
    }

    public function clearSectors(): void
    {
        $this->selectedSectors = [];
        $this->resetPage();
    }

    public function selectAllCountries(): void
    {
        $this->selectedCountries = $this->countries;
        $this->resetPage();
    }

    public function clearCountries(): void
    {
        $this->selectedCountries = [];
        $this->resetPage();
    }

    public function render(): View
    {
        $selectedSectors = array_values(array_intersect(array_filter($this->selectedSectors, 'is_string'), $this->sectors));
        $selectedCountries = array_values(array_intersect(array_filter($this->selectedCountries, 'is_string'), $this->countries));
        $query = GrantOpportunity::query()->available()
            ->when($this->status === 'open', fn ($query) => $query->open())
            ->when($this->status === 'closed', fn ($query) => $query->closed())
            ->when($this->status === 'all', fn ($query) => $query->whereNotIn('status', ['draft', 'archived']))
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when($this->type !== 'all', fn ($query) => $query->where('funding_type', $this->type === 'grant' ? 'Grant' : 'Partnership'))
            ->when(array_diff($this->sectors, $selectedSectors) !== [] || $this->selectedSectors !== $selectedSectors, function ($query) use ($selectedSectors): void {
                if ($selectedSectors === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(function ($query) use ($selectedSectors): void {
                    foreach ($selectedSectors as $sector) {
                        $query->orWhereJsonContains('thematic_sectors', $sector);
                    }
                });
            })
            ->when(array_diff($this->countries, $selectedCountries) !== [] || $this->selectedCountries !== $selectedCountries, function ($query) use ($selectedCountries): void {
                if ($selectedCountries === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(function ($query) use ($selectedCountries): void {
                    foreach ($selectedCountries as $country) {
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
