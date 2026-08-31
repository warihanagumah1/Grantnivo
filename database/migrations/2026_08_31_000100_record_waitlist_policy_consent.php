<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table): void {
            // Existing signups remain null: do not invent retrospective consent.
            $table->timestamp('consented_at')->nullable();
            $table->string('privacy_policy_version', 32)->nullable();
            $table->string('terms_version', 32)->nullable();
            $table->string('consent_version', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('waitlist_entries', function (Blueprint $table): void {
            $table->dropColumn(['consented_at', 'privacy_policy_version', 'terms_version', 'consent_version']);
        });
    }
};
