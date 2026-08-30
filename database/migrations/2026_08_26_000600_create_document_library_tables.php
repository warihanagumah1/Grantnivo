<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['organisation_id', 'name']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('document_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('original_filename');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size');
            $table->string('storage_path')->unique();
            $table->string('processing_status', 30)->index();
            $table->string('extraction_status', 30)->index();
            $table->string('indexed_status', 30)->index();
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->index(['organisation_id', 'uploaded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
