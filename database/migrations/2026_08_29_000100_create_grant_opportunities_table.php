<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grant_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('funder');
            $table->string('funding_programme')->nullable();
            $table->text('summary');
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('minimum_award')->nullable();
            $table->unsignedBigInteger('maximum_award')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->date('open_date')->nullable();
            $table->date('deadline')->nullable()->index();
            $table->json('eligible_countries')->nullable();
            $table->json('eligible_organisation_types')->nullable();
            $table->json('thematic_sectors')->nullable();
            $table->text('eligibility_requirements')->nullable();
            $table->string('funding_type')->nullable();
            $table->string('opportunity_url', 2048)->nullable();
            $table->string('status', 30)->index();
            $table->boolean('featured')->default(false)->index();
            $table->timestamp('source_verified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'deadline']);
            $table->index(['funder', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grant_opportunities');
    }
};
