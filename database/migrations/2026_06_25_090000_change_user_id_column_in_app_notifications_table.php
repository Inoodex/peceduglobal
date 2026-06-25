<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            // Drop index first to avoid type issues with indexed columns
            $table->dropIndex(['user_id', 'is_read']);
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            // Change column type to string (since user ids are UUIDs)
            $table->string('user_id', 36)->nullable()->change();
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            // Re-create the index
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::table('app_notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('app_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
        });
    }
};
