<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Application-level notifications table.
 *
 * NOTE: Named `app_notifications` (not `notifications`) to avoid clashing with
 * Laravel's built-in database notification table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();

            // Notification type, e.g. "chat_message", "system", "booking".
            $table->string('type')->nullable();

            // Short human-readable title, e.g. "New message from guest@example.com".
            $table->string('title');

            // Optional preview / body text (truncated message content, etc.).
            $table->text('body')->nullable();

            // Full JSON payload (message object, extra metadata).
            $table->json('data')->nullable();

            // Frontend route to navigate to on click.
            $table->string('action_url')->nullable();

            // Extra params for navigation, e.g. {"conversationId": 5}.
            $table->json('action_params')->nullable();

            // Read status.
            $table->boolean('is_read')->default(false);

            // Target admin user (null = broadcast to all admins).
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            // Indexes for the common queries (inbox list, unread badge count).
            $table->index(['user_id', 'is_read']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
