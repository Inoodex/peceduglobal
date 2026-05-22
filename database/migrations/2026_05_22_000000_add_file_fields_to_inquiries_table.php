<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('additional_info_file_name')->nullable()->after('additional_info');
            $table->string('additional_info_file_path')->nullable()->after('additional_info_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['additional_info_file_name', 'additional_info_file_path']);
        });
    }
};
