<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->foreignUuid('consultant_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['available', 'booked', 'cancelled'])->default('available');
            $table->timestamps();
            
            $table->unique(['consultant_id', 'slot_id'], 'unique_consultant_slot');
            $table->index('status');
            $table->index('consultant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_availability');
    }
};
