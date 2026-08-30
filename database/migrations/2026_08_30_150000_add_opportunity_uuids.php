<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grant_opportunities', fn (Blueprint $table) => $table->uuid('uuid')->nullable()->unique());

        // Include archived and soft-deleted records, preserving their existing IDs and relationships.
        DB::table('grant_opportunities')->orderBy('id')->chunkById(200, function ($opportunities): void {
            foreach ($opportunities as $opportunity) {
                DB::table('grant_opportunities')->where('id', $opportunity->id)->update(['uuid' => (string) Str::uuid7()]);
            }
        });

        Schema::table('grant_opportunities', fn (Blueprint $table) => $table->uuid('uuid')->nullable(false)->change());
    }

    public function down(): void
    {
        Schema::table('grant_opportunities', function (Blueprint $table): void {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
