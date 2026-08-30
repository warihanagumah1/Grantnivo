<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class V1ArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_data_is_encrypted_and_can_be_restored_without_losing_membership(): void
    {
        $migration = require database_path('migrations/2026_08_30_000200_archive_retired_v2_workspaces.php');
        $migration->down();
        $user = User::factory()->create();
        $id = DB::table('organisations')->insertGetId(['legal_name' => 'Private Organisation', 'mission' => 'Private mission']);
        DB::table('organisation_user')->insert(['organisation_id' => $id, 'user_id' => $user->id, 'role' => 'owner']);
        DB::table('users')->where('id', $user->id)->update(['current_organisation_id' => $id]);
        $migration->up();
        $this->assertFalse(Schema::hasTable('organisations'));
        $this->assertFalse(Schema::hasColumn('users', 'current_organisation_id'));
        $this->assertDatabaseCount('legacy_v2_archive', 3);
        $payload = DB::table('legacy_v2_archive')->where('source_table', 'organisations')->value('encrypted_payload');
        $this->assertStringNotContainsString('Private mission', $payload);
        $this->assertStringContainsString('Private mission', Crypt::decryptString($payload));
        $migration->down();
        $this->assertDatabaseHas('organisations', ['id' => $id, 'mission' => 'Private mission']);
        $this->assertDatabaseHas('organisation_user', ['organisation_id' => $id, 'user_id' => $user->id, 'role' => 'owner']);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'current_organisation_id' => $id]);
        $migration->up();
    }
}
