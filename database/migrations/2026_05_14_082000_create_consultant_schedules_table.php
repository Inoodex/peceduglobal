<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('consultant_id')->constrained('users')->onDelete('cascade');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('day_of_week');
            $table->enum('status', ['available', 'booked'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_schedules');
    }
};
