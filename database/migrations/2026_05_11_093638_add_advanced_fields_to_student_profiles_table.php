<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extra columns not present in create_student_profiles_table.
     * (gender, nationality, alternative_phone — father_name etc. already exist on base table.)
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'gender')) {
                $table->string('gender')->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('student_profiles', 'nationality')) {
                $table->string('nationality')->nullable()->after('passport_validity');
            }
            if (! Schema::hasColumn('student_profiles', 'alternative_phone')) {
                $table->string('alternative_phone')->nullable()->after('sponsor_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('student_profiles', 'gender')) {
                $cols[] = 'gender';
            }
            if (Schema::hasColumn('student_profiles', 'nationality')) {
                $cols[] = 'nationality';
            }
            if (Schema::hasColumn('student_profiles', 'alternative_phone')) {
                $cols[] = 'alternative_phone';
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
