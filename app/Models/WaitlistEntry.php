<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'name', 'organisation', 'source', 'subscribed_at', 'unsubscribed_at', 'consented_at', 'privacy_policy_version', 'terms_version', 'consent_version'])]
class WaitlistEntry extends Model
{
    protected function casts(): array
    {
        return ['subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'consented_at' => 'datetime'];
    }
}
