<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('block_elements', function (Blueprint $table) {
            $table->json('image_paths')->nullable();
            $table->renameColumn('image_path', 'old_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('block_elements', function (Blueprint $table) {
            $table->dropColumn('image_paths');
            $table->renameColumn('old_image_path', 'image_path');
        });
    }
};
