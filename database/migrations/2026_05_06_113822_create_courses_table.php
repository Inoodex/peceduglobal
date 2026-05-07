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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('level')->nullable(); // e.g., Undergraduate, Postgraduate, PhD
            $table->string('intake')->nullable(); // e.g., Fall, Spring, Summer
            $table->string('duration')->nullable(); // e.g., 3 Years, 4 Years
            $table->string('ielts_requirement')->nullable();
            $table->decimal('tuition_fee', 12, 2)->nullable();
            $table->text('requirements')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
