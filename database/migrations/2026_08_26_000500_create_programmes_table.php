<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->index();
            $table->json('geography')->nullable();
            $table->text('target_population')->nullable();
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->text('outcomes')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
