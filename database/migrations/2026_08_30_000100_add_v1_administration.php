<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->index();
        });
        Schema::table('grant_opportunities', function (Blueprint $table) {
            $table->string('learn_more_url', 2048)->nullable();
            $table->softDeletes();
        });
        Schema::rename('newsletter_subscriptions', 'waitlist_entries');
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_hash', 64)->index();
            $table->char('session_hash', 64)->unique();
            $table->timestamp('created_at')->index();
        });
        Schema::create('opportunity_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grant_opportunity_id')->constrained()->cascadeOnDelete();
            $table->char('session_hash', 64);
            $table->string('kind', 20);
            $table->date('event_date');
            $table->timestamp('created_at')->index();
            $table->unique(['grant_opportunity_id', 'session_hash', 'kind', 'event_date'], 'opportunity_event_unique');
            $table->index(['kind', 'created_at']);
        });
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('subject_type', 80);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('opportunity_events');
        Schema::dropIfExists('site_visits');
        Schema::rename('waitlist_entries', 'newsletter_subscriptions');
        Schema::table('grant_opportunities', function (Blueprint $table) {
            $table->dropColumn(['learn_more_url', 'deleted_at']);
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_admin']);
            $table->dropColumn('is_admin');
        });
    }
};
