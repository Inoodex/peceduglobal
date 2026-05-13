<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Runs after create_courses_table so country_id / university_id / course_id FKs resolve.
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            // $table->string('application_id')->unique();

            $table->string('phone');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('sponsor_phone')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_validity')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();

            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('university_id')->nullable()->constrained('universities');
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->string('preferred_intake')->nullable();

            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('ielts_score', 3, 1)->nullable();

            $table->json('documents')->nullable();
            $table->json('translation_documents')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
