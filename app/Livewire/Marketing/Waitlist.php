<?php

namespace App\Livewire\Marketing;

use App\Actions\Marketing\JoinWaitlist;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.marketing')]
#[Title('Join the Waitlist')]
class Waitlist extends Component
{
    public string $name = '';

    public string $email = '';

    public string $organisation = '';

    public string $website = '';

    public bool $submitted = false;

    public bool $acceptPolicies = false;

    public function join(JoinWaitlist $join): void
    {
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }
        $join->handle(['name' => $this->name, 'email' => $this->email, 'organisation' => $this->organisation, 'acceptPolicies' => $this->acceptPolicies], request()->ip() ?? 'unknown');
        $this->reset('name', 'organisation');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.marketing.waitlist');
    }
}
