<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('contact_name', 120);
            $table->string('contact_role', 120);
            $table->string('contact_avatar', 10);
            $table->string('business_name', 140);
            $table->string('business_revenue', 40)->default('$1.8M');
            $table->string('business_sde', 40)->default('$420K');
            $table->string('business_asking', 40)->default('$1.9M');
            $table->unsignedTinyInteger('business_fit')->default(90);
            $table->text('last_message')->nullable();
            $table->string('time_ago', 40)->default('Just now');
            $table->boolean('unread')->default(false);
            $table->string('category', 40)->default('opportunities'); // opportunities | brokers
            $table->boolean('online')->default(false);
            $table->boolean('nda_signed')->default(true);
            $table->boolean('call_scheduled')->default(false);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_thread_id')->constrained('message_threads')->cascadeOnDelete();
            $table->string('sender', 20)->default('me'); // me | them
            $table->string('name', 120);
            $table->string('time_label', 60)->default('Just now');
            $table->text('text');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('title', 160);
            $table->string('body', 255);
            $table->string('type', 40)->default('match'); // match | market | message | system
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email', 160)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 60)->default('dashboard_widget');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('message_threads');
    }
};
