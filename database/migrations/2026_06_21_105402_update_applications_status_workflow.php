<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Switches the applications.status column from a fixed enum to a flexible
 * string column supporting the new 7-step application workflow:
 *
 *   1. document_submitted
 *   2. application
 *   3. offer_letter
 *   4. deposit_received
 *   5. enrollment_confirmed
 *   6. visa_processing
 *   7. enrolled
 *
 * Existing legacy statuses are mapped onto the new workflow so no data is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert the enum column into a flexible string column FIRST
        //    so that new status values are accepted by MySQL.
        DB::statement("ALTER TABLE applications MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'document_submitted'");

        // 2. Map legacy status values onto the new workflow.
        DB::table('applications')->whereIn('status', ['pending', 'document_review'])
            ->update(['status' => 'document_submitted']);
        DB::table('applications')->where('status', 'university_submitted')
            ->update(['status' => 'application']);
        DB::table('applications')->where('status', 'visa_process')
            ->update(['status' => 'visa_processing']);
        DB::table('applications')->where('status', 'completed')
            ->update(['status' => 'enrolled']);
        // 'offer_letter' and 'rejected' stay as-is.
    }

    public function down(): void
    {
        // 1. Restore the legacy enum column.
        DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM('pending','document_review','university_submitted','offer_letter','visa_process','completed','rejected') NOT NULL DEFAULT 'pending'");

        // 2. Map the new workflow values back to legacy statuses.
        DB::table('applications')->where('status', 'document_submitted')->update(['status' => 'pending']);
        DB::table('applications')->where('status', 'application')->update(['status' => 'university_submitted']);
        DB::table('applications')->where('status', 'visa_processing')->update(['status' => 'visa_process']);
        DB::table('applications')->where('status', 'enrolled')->update(['status' => 'completed']);
        // Any remaining new-only statuses fall back to pending.
        DB::table('applications')->whereIn('status', ['deposit_received', 'enrollment_confirmed'])->update(['status' => 'pending']);
    }
};
