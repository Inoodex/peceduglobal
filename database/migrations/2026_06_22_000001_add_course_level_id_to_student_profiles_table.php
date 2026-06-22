<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Student's preferred course level for study preferences.
     * References the course_levels table (e.g. Bachelor, Masters, Diploma etc.)
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'course_level_id')) {
                $table->foreignId('course_level_id')->nullable()->after('country_id')->constrained('course_levels')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'course_level_id')) {
                $table->dropForeign(['course_level_id']);
                $table->dropColumn('course_level_id');
            }
        });
    }
};
