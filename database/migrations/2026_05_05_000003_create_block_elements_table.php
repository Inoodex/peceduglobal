<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_block_id')->constrained()->cascadeOnDelete();
            $table->string('element_title')->nullable();
            $table->text('element_body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('link_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_elements');
    }
};
