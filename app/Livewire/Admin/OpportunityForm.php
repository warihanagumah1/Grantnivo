<?php

namespace App\Livewire\Admin;

use App\Actions\Grants\SaveOpportunity;
use App\Models\GrantOpportunity;
use App\Services\Grants\RichText;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class OpportunityForm extends AdminPage
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $image;

    public string $imageSource = 'url';

    #[Locked]
    public ?string $savedImageUrl = null;

    #[Locked]
    public string $savedStatus = 'draft';

    #[Locked]
    public ?int $opportunityId = null;

    /** @var array<string, mixed> */
    public array $form = [
        'title' => '', 'funder' => '', 'sector' => '', 'countries' => '',
        'funding_type' => 'Grant', 'status' => 'active', 'maximum_award' => '', 'currency' => 'USD',
        'deadline' => '', 'summary' => '', 'description' => '', 'image_url' => '', 'image_alt' => '',
        'funder_url' => '', 'learn_more_url' => '', 'opportunity_url' => '',
    ];

    public function mount(?GrantOpportunity $grantOpportunity = null): void
    {
        if (! $grantOpportunity?->exists) {
            return;
        }
        $this->opportunityId = $grantOpportunity->id;
        $this->savedStatus = $grantOpportunity->status->value;
        $this->imageSource = $grantOpportunity->image_path ? 'upload' : 'url';
        $this->savedImageUrl = $grantOpportunity->image_path ? $grantOpportunity->imageUrl() : null;
        foreach (array_keys($this->form) as $key) {
            $this->form[$key] = match ($key) {
                'sector' => implode(', ', $grantOpportunity->thematic_sectors ?? []),
                'countries' => implode(', ', $grantOpportunity->eligible_countries ?? []),
                'status' => $this->savedStatus === 'draft' ? 'active' : $this->savedStatus,
                'summary', 'description' => app(RichText::class)->sanitize($grantOpportunity->{$key}),
                'deadline' => $grantOpportunity->deadline?->format('Y-m-d') ?? '',
                default => $grantOpportunity->{$key} ?? '',
            };
        }
    }

    public function save(SaveOpportunity $save): void
    {
        $this->persist($save, $this->form['status']);
    }

    public function saveDraft(SaveOpportunity $save): void
    {
        $this->persist($save, 'draft');
    }

    public function updatedImage(): void
    {
        $this->validate(['image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000']]);
    }

    private function persist(SaveOpportunity $save, string $status): void
    {
        $this->resetValidation();
        try {
            $save->handle(auth()->user(), array_replace($this->form, ['status' => $status, 'image_source' => $this->imageSource]), $this->opportunityId ? GrantOpportunity::query()->findOrFail($this->opportunityId) : null, $this->imageSource === 'upload' ? $this->image : null);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $key = match (true) {
                    $field === 'image' => 'image',
                    $field === 'image_source' => 'imageSource',
                    str_starts_with($field, 'countries.') => 'form.countries',
                    default => 'form.'.$field,
                };
                $this->addError($key, $messages[0]);
            }

            return;
        }
        session()->flash('success', $status === 'draft' ? 'Draft saved. It is not visible on the public website.' : ($this->savedStatus === 'draft' ? 'Opportunity published.' : 'Opportunity updated.'));
        $this->redirectRoute('admin.opportunities');
    }

    public function render(): View
    {
        return view('livewire.admin.opportunity-form')->title($this->opportunityId ? 'Edit opportunity' : 'New opportunity');
    }
}
