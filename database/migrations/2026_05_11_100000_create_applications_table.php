<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignUuid('consultant_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('university_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('intake_id')->constrained('course_intakes')->cascadeOnDelete();
            $table->string('application_number')->unique();
            $table->string('course_name');
            $table->enum('application_type', ['undergraduate', 'postgraduate', 'phd', 'diploma'])->default('undergraduate');
            $table->enum('status', ['pending', 'document_review', 'university_submitted', 'offer_letter', 'visa_process', 'completed', 'rejected'])->default('pending');
            $table->json('document_checklist')->nullable();
            $table->json('consultant_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
