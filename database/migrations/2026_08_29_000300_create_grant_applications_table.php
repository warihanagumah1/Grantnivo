<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grant_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grant_opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->string('funder');
            $table->string('status', 30)->index();
            $table->date('deadline')->nullable()->index();
            $table->date('submission_date')->nullable();
            $table->unsignedBigInteger('amount_requested')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'status']);
            $table->index(['organisation_id', 'deadline']);
            $table->unique(['organisation_id', 'grant_opportunity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grant_applications');
    }
};
