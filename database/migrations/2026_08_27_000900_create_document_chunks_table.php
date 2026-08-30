<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->text('processing_error')->nullable()->after('indexed_status');
            $table->timestamp('processing_started_at')->nullable()->after('processing_error');
            $table->timestamp('extracted_at')->nullable()->after('processing_started_at');
            $table->timestamp('indexed_at')->nullable()->after('extracted_at');
        });

        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sequence');
            $table->longText('content');
            $table->char('content_hash', 64);
            $table->unsignedInteger('page_number')->nullable();
            $table->string('section_heading')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('character_count');
            $table->unsignedInteger('token_count');
            $table->json('embedding')->nullable();
            $table->string('embedding_model')->nullable();
            $table->timestamp('embedded_at')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'sequence']);
            $table->index(['organisation_id', 'document_id']);
            $table->index(['organisation_id', 'document_category_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE document_chunks ADD FULLTEXT document_chunks_content_fulltext (content, section_heading)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'processing_error',
                'processing_started_at',
                'extracted_at',
                'indexed_at',
            ]);
        });
    }
};
