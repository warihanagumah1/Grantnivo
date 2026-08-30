<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuditLog
{
    /** @param array<string, mixed> $changes */
    public function record(User $user, string $action, string $subject, ?int $id = null, array $changes = []): void
    {
        DB::table('admin_audit_logs')->insert([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject,
            'subject_id' => $id,
            'changes' => json_encode($changes, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
    }
}
