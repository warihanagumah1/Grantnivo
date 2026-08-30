<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grant_opportunity_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organisation_id', 'user_id', 'grant_opportunity_id'], 'saved_opportunities_unique');
            $table->index(['organisation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_opportunities');
    }
};
