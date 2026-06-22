<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the student's highest completed education level.
     * CGPA scales differ by level (SSC/HSC out of 5.00, Bachelor/Master out of 4.00),
     * so this column is paired with the existing cgpa column.
     *
     * Allowed values: bachelor, masters, postgraduate, diploma, hsc, ssc
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'last_education_level')) {
                $table->string('last_education_level')->nullable()->after('cgpa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'last_education_level')) {
                $table->dropColumn('last_education_level');
            }
        });
    }
};
