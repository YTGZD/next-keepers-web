<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('buyer')->after('email'); // buyer | owner | broker | investor | admin
            $table->string('phone', 40)->nullable()->after('password');
            $table->string('title', 120)->nullable();
            $table->string('company', 140)->nullable();
            $table->string('location', 120)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('buyer_type', 100)->nullable();
            $table->json('focus_industries')->nullable();
            $table->string('target_deal_size', 80)->nullable();
            $table->string('min_sde', 80)->nullable();
            $table->string('capital_readiness', 160)->nullable();
            $table->string('geographic_preference', 180)->nullable();
            $table->string('membership_tier', 60)->default('Free account');
            $table->string('member_since', 40)->nullable();
            $table->string('nda_status', 80)->default('No NDA on file');
            $table->timestamp('nda_signed_at')->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('notify_instant_deals')->default(true);
            $table->boolean('notify_weekly_intel')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_marketing')->default(false);
            $table->unsignedInteger('viewed_deals_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'phone', 'title', 'company', 'location', 'avatar', 'bio',
                'buyer_type', 'focus_industries', 'target_deal_size', 'min_sde',
                'capital_readiness', 'geographic_preference', 'membership_tier',
                'member_since', 'nda_status', 'nda_signed_at', 'linkedin_url',
                'website_url', 'two_factor_enabled', 'notify_instant_deals',
                'notify_weekly_intel', 'notify_messages', 'notify_marketing',
                'viewed_deals_count',
            ]);
        });
    }
};
