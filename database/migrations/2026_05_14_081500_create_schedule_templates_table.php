<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->enum('day_of_week', ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->integer('slot_duration')->default(30); // minutes
            $table->integer('buffer_time')->default(0); // minutes between slots
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['day_of_week', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};
