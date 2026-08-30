<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('public_name')->nullable();
            $table->string('organisation_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('registration_country', 2)->nullable();
            $table->unsignedSmallInteger('year_established')->nullable();
            $table->string('website')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('address')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->text('description')->nullable();
            $table->json('geographic_focus')->nullable();
            $table->json('thematic_sectors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
