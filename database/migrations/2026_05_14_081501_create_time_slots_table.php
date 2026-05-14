<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('day_of_week', ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']);
            $table->enum('status', ['open', 'closed', 'holiday'])->default('open');
            $table->timestamps();
            
            $table->index('slot_date');
            $table->index('status');
            $table->unique(['slot_date', 'start_time', 'end_time'], 'unique_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
