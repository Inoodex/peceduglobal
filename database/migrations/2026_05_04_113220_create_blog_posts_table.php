<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image_url')->nullable();
            $table->string('featured_image_alt')->nullable();

            $table->enum('status', ['draft', 'in_review', 'published', 'scheduled', 'archived'])->default('draft');
            $table->datetime('published_at')->nullable();
            $table->datetime('scheduled_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->string('canonical_url')->nullable();

            // Analytics
            // $table->integer('read_time_minutes')->default(1);
            // $table->unsignedBigInteger('view_count')->default(0);
            // $table->unsignedInteger('share_count')->default(0);

            // Targeting
            $table->string('target_country')->nullable();
            // $table->enum('target_audience', ['students', 'parents', 'partners', 'all'])->default('all');
            $table->boolean('is_featured')->default(false);
            // $table->boolean('allow_comments')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('author_id');
            $table->index('blog_category_id');
            $table->index('status');
            $table->index('published_at');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
