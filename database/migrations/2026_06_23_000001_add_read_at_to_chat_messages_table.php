<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Precise timestamp for when a message was read (for ✓✓ receipts).
            // Kept nullable so existing rows remain "unread" until acted upon.
            if (!Schema::hasColumn('chat_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });

        // Backfill read_at for any messages that were already flagged is_read
        // before this column existed, so historical receipts stay consistent.
        DB::table('chat_messages')
            ->where('is_read', true)
            ->whereNull('read_at')
            ->update(['read_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
