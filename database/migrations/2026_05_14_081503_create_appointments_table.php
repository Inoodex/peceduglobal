<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_id')->constrained('consultant_availability')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->enum('meeting_type', ['online', 'physical'])->default('online');
            $table->string('meeting_link')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->unique(['student_id', 'availability_id'], 'unique_student_slot');
            $table->index('status');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
