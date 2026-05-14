<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Remove unnecessary and static fields
            if (Schema::hasColumn('applications', 'application_type')) {
                $table->dropColumn('application_type');
            }
            if (Schema::hasColumn('applications', 'document_checklist')) {
                $table->dropColumn('document_checklist');
            }
            if (Schema::hasColumn('applications', 'consultant_remarks')) {
                $table->dropColumn('consultant_remarks');
            }

            // Add dynamic Course Level relation
            $table->foreignId('course_level_id')->nullable()->after('course_id')->constrained('course_levels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['course_level_id']);
            $table->dropColumn('course_level_id');
            $table->enum('application_type', ['undergraduate', 'postgraduate', 'phd', 'diploma'])->default('undergraduate');
            $table->json('document_checklist')->nullable();
            $table->json('consultant_remarks')->nullable();
        });
    }
};
