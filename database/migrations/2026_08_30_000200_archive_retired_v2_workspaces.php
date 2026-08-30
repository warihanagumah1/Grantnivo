<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'organisations', 'organisation_user', 'programmes', 'document_categories',
        'documents', 'impact_metrics', 'structured_facts', 'document_chunks',
        'saved_opportunities', 'grant_applications',
    ];

    public function up(): void
    {
        // Keep retired tenant data encrypted and completely outside all public/admin models.
        if (! Schema::hasTable('legacy_v2_archive')) {
            Schema::create('legacy_v2_archive', function (Blueprint $table) {
                $table->id();
                $table->string('source_table', 80);
                $table->unsignedBigInteger('source_id');
                $table->longText('encrypted_payload');
                $table->timestamp('archived_at');
                $table->unique(['source_table', 'source_id']);
            });
        }
        DB::transaction(function (): void {
            foreach ($this->tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        $this->archive($table, (array) $row);
                    }
                });
                if (DB::table($table)->count() !== DB::table('legacy_v2_archive')->where('source_table', $table)->count()) {
                    throw new RuntimeException('Archive count mismatch for '.$table.'. No tables were removed.');
                }
            }
            if (Schema::hasColumn('users', 'current_organisation_id')) {
                foreach (DB::table('users')->whereNotNull('current_organisation_id')->get(['id', 'current_organisation_id']) as $user) {
                    $this->archive('user_workspace', (array) $user);
                }
            }
        });

        // All copies are committed before any schema is retired (including on MySQL).
        if (Schema::hasColumn('users', 'current_organisation_id')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('current_organisation_id'));
        }
        foreach (array_reverse($this->tables) as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        foreach ([
            '2026_08_26_000100_create_organisations_table.php',
            '2026_08_26_000200_create_organisation_memberships_table.php',
            '2026_08_26_000300_add_current_organisation_id_to_users_table.php',
            '2026_08_26_000400_add_brain_profile_fields_to_organisations_table.php',
            '2026_08_26_000500_create_programmes_table.php',
            '2026_08_26_000600_create_document_library_tables.php',
            '2026_08_26_000700_create_knowledge_tables.php',
            '2026_08_27_000900_create_document_chunks_table.php',
            '2026_08_29_000200_create_saved_opportunities_table.php',
            '2026_08_29_000300_create_grant_applications_table.php',
        ] as $file) {
            (require __DIR__.'/'.$file)->up();
        }
        DB::transaction(function (): void {
            foreach ($this->tables as $table) {
                foreach (DB::table('legacy_v2_archive')->where('source_table', $table)->orderBy('source_id')->cursor() as $record) {
                    DB::table($table)->insert(json_decode(Crypt::decryptString($record->encrypted_payload), true, 512, JSON_THROW_ON_ERROR));
                }
                if (DB::getDriverName() === 'pgsql') {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1), EXISTS(SELECT 1 FROM {$table}))");
                }
            }
            foreach (DB::table('legacy_v2_archive')->where('source_table', 'user_workspace')->cursor() as $record) {
                $row = json_decode(Crypt::decryptString($record->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
                DB::table('users')->where('id', $row['id'])->update(['current_organisation_id' => $row['current_organisation_id']]);
            }
        });
        Schema::dropIfExists('legacy_v2_archive');
    }

    /** @param array<string, mixed> $row */
    private function archive(string $table, array $row): void
    {
        $json = json_encode($row, JSON_THROW_ON_ERROR);
        $encrypted = Crypt::encryptString($json);
        if (Crypt::decryptString($encrypted) !== $json) {
            throw new RuntimeException('Archive verification failed.');
        }
        DB::table('legacy_v2_archive')->updateOrInsert(
            ['source_table' => $table, 'source_id' => $row['id']],
            ['encrypted_payload' => $encrypted, 'archived_at' => now()],
        );
    }
};
