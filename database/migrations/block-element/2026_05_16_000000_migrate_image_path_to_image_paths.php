<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('block_elements', function (Blueprint $table) {
            if (!Schema::hasColumn('block_elements', 'image_paths')) {
                $table->json('image_paths')->nullable()->after('element_body');
            }
        });

        // Copy existing image_path values into image_paths as single-item arrays
        try {
            \DB::table('block_elements')->whereNotNull('image_path')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $r) {
                    \DB::table('block_elements')->where('id', $r->id)->update([
                        'image_paths' => json_encode([$r->image_path]),
                    ]);
                }
            });
        } catch (\Exception $e) {
            // ignore during migration if table/column not present in some environments
        }

        Schema::table('block_elements', function (Blueprint $table) {
            if (Schema::hasColumn('block_elements', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('block_elements', function (Blueprint $table) {
            if (!Schema::hasColumn('block_elements', 'image_path')) {
                $table->string('image_path')->nullable()->after('element_body');
            }
        });

        // Try to copy back from image_paths to image_path (first item)
        try {
            \DB::table('block_elements')->whereNotNull('image_paths')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $r) {
                    $paths = json_decode($r->image_paths, true);
                    $first = is_array($paths) && count($paths) ? $paths[0] : null;
                    \DB::table('block_elements')->where('id', $r->id)->update([
                        'image_path' => $first,
                    ]);
                }
            });
        } catch (\Exception $e) {
            // ignore
        }

        Schema::table('block_elements', function (Blueprint $table) {
            if (Schema::hasColumn('block_elements', 'image_paths')) {
                $table->dropColumn('image_paths');
            }
        });
    }
};
