<?php

namespace App\Livewire\Admin;

use App\Actions\Grants\ImportOpportunities;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Bulk import opportunities')]
class OpportunityImport extends AdminPage
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $file;

    public function updatedFile(): void
    {
        $this->resetValidation();
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'extensions:csv', 'max:2048']]);
    }

    public function import(ImportOpportunities $import): void
    {
        $this->resetValidation();
        $this->validate(['file' => ['required', 'file']]);
        try {
            $count = $import->handle(auth()->user(), $this->file);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }
        session()->flash('success', trans_choice(':count opportunity imported as a draft.|:count opportunities imported as drafts.', $count).' Review and publish each one when ready.');
        $this->redirectRoute('admin.opportunities', ['status' => 'draft']);
    }

    public function render(): View
    {
        return view('livewire.admin.opportunity-import');
    }
}
