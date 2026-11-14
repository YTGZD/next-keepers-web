<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buy_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('industries');
            $table->string('location', 140)->default('New York, NY + 100 miles');
            $table->unsignedInteger('radius_miles')->default(100);
            $table->unsignedBigInteger('min_revenue')->default(500000);
            $table->unsignedBigInteger('max_revenue')->default(5000000);
            $table->string('revenue_range', 60)->default('$500K – $3M');
            $table->unsignedBigInteger('min_sde')->default(200000);
            $table->unsignedBigInteger('max_sde')->default(1500000);
            $table->string('sde_range', 60)->default('$200K – $1.5M');
            $table->unsignedBigInteger('min_asking_price')->default(500000);
            $table->unsignedBigInteger('max_asking_price')->default(3500000);
            $table->string('asking_price', 60)->default('$500K – $3M');
            $table->decimal('min_profit_margin', 5, 2)->default(15.00);
            $table->string('profit_margin', 40)->default('≥ 15%');
            $table->unsignedInteger('min_employees')->default(5);
            $table->unsignedInteger('max_employees')->default(50);
            $table->string('employees', 40)->default('5 – 50');
            $table->boolean('profitable_only')->default(true);
            $table->boolean('owner_operated')->default(false);
            $table->boolean('growing_market')->default(true);
            $table->boolean('absentee_owner_ok')->default(false);
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('tag', 40)->default('Watchlist'); // High Fit | New | Watchlist | Alert
            $table->string('tag_type', 20)->default('teal'); // green | amber | teal | orange
            $table->string('tag_icon', 20)->default('bookmark'); // star | bolt | bookmark | bell
            $table->string('category', 40)->default('all'); // high_fit | recently_added | alerts | all
            $table->string('added_time_label', 60)->default('Added recently');
            $table->unsignedTinyInteger('fit_score')->default(88);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'business_id']);
        });

        Schema::create('pipeline_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('stage', 40)->default('saved')->index(); // saved | inquiry_sent | nda_signed | cim_review | loi | due_diligence | closed
            $table->boolean('nda_signed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'business_id']);
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('type', 40)->default('dossier'); // dossier | advisory_contact
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('buyer_entity', 160)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('received'); // received | nda_pending | cim_shared | closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('pipeline_deals');
        Schema::dropIfExists('watchlist_items');
        Schema::dropIfExists('buy_boxes');
    }
};
