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
            $table->string('element_type')
                ->default('text')
                ->after('page_block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('block_elements', function (Blueprint $table) {
            Schema::table('block_elements', function (Blueprint $table) {
                $table->dropColumn('element_type');
            });
        });
    }
};
