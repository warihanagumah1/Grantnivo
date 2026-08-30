<?php

namespace App\Actions\Grants;

use App\Enums\OpportunityStatus;
use App\Models\GrantOpportunity;
use App\Models\User;
use App\Services\Admin\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ArchiveOpportunity
{
    public function __construct(private AuditLog $audit) {}

    public function handle(User $user, GrantOpportunity $opportunity, bool $restore = false): void
    {
        Gate::forUser($user)->authorize($restore ? 'restore' : 'delete', $opportunity);
        DB::transaction(function () use ($user, $opportunity, $restore): void {
            if ($restore && $opportunity->status === OpportunityStatus::Archived) {
                $opportunity->status = OpportunityStatus::Draft;
                $opportunity->save();
            }
            $restore ? $opportunity->restore() : $opportunity->delete();
            $this->audit->record($user, $restore ? 'opportunity.restored' : 'opportunity.archived', 'grant_opportunity', $opportunity->id);
        });
    }
}
