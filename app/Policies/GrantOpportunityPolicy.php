<?php

namespace App\Policies;

use App\Models\GrantOpportunity;
use App\Models\User;

class GrantOpportunityPolicy
{
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, GrantOpportunity $opportunity): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, GrantOpportunity $opportunity): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, GrantOpportunity $opportunity): bool
    {
        return $user->is_admin;
    }
}
