<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impact_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('indicator');
            $table->string('value');
            $table->string('unit')->nullable();
            $table->string('reporting_period')->nullable();
            $table->string('geography')->nullable();
            $table->string('source_reference')->nullable();
            $table->date('fact_date')->nullable();
            $table->string('verification_status', 30)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'indicator']);
        });

        Schema::create('structured_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('entered_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 50)->index();
            $table->string('label');
            $table->text('value');
            $table->string('source_reference')->nullable();
            $table->date('fact_date')->nullable();
            $table->string('verification_status', 30)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['organisation_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('structured_facts');
        Schema::dropIfExists('impact_metrics');
    }
};
