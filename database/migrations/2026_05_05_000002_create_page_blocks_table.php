<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('block_type'); // e.g., 'hero', 'grid', 'table', 'faq', 'university_list', 'scholarship_list'
            $table->string('section_title')->nullable();
            $table->text('section_description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // For layout, colors, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
