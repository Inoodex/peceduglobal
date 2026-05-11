<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('father_name')->nullable()->after('phone');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->date('date_of_birth')->nullable()->after('mother_name');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('passport_number')->nullable()->after('gender');
            $table->date('passport_expiry')->nullable()->after('passport_number');
            $table->string('nationality')->nullable()->after('passport_expiry');
            $table->string('alternative_phone')->nullable()->after('nationality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'father_name',
                'mother_name',
                'date_of_birth',
                'gender',
                'passport_number',
                'passport_expiry',
                'nationality',
                'alternative_phone'
            ]);
        });
    }
};
