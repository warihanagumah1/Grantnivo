<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grant_opportunities', function (Blueprint $table) {
            $table->text('image_url')->nullable()->after('opportunity_url');
            $table->string('image_alt')->nullable()->after('image_url');
            $table->text('funder_url')->nullable()->after('image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('grant_opportunities', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'image_alt', 'funder_url']);
        });
    }
};
