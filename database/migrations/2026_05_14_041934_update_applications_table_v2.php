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
        Schema::table('applications', function (Blueprint $table) {
            // New fields for the form
            $table->foreignId('country_id')->nullable()->constrained('countries')->after('student_id');
            $table->text('notes')->nullable()->after('status');
            
            // Workflow management
            $table->string('university_reference_id')->nullable()->after('application_number');
            $table->text('rejection_reason')->nullable()->after('notes');
            
            // Workflow timestamps
            $table->timestamp('applied_at')->nullable()->after('rejection_reason');
            
            // Adjust existing columns
            $table->string('application_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'country_id',
                'notes',
                'university_reference_id',
                'rejection_reason',
                'applied_at',
            ]);
            $table->string('application_number')->nullable(false)->change();
        });
    }
};
