<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 140)->unique();
            $table->string('name', 140);
            $table->string('short_name', 140)->nullable();
            $table->string('tagline', 255)->nullable();
            $table->text('description');
            $table->text('overview_description')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->string('industry', 100);
            $table->string('category', 40)->default('all')->index(); // hvac | healthcare | manufacturing | services | retail
            $table->foreignId('regional_market_id')->nullable()->constrained('regional_markets')->nullOnDelete();
            $table->string('city', 80);
            $table->string('state', 10)->default('NY');
            $table->string('location', 120)->index(); // e.g. "Rochester, NY"
            $table->string('service_area', 140)->default('50-mile radius');
            $table->unsignedSmallInteger('established_year')->default(2012);
            $table->unsignedInteger('employees')->default(10);
            $table->string('annual_growth_label', 80)->default('8% (last 3 years)');

            // Financial metrics stored as integer dollars for exact querying/sorting + formatted labels
            $table->unsignedBigInteger('annual_revenue')->default(1800000)->index();
            $table->string('revenue_label', 40)->default('$1.8M');
            $table->decimal('revenue_growth_percent', 5, 2)->default(8.00);

            $table->unsignedBigInteger('sde')->default(420000)->index();
            $table->string('sde_label', 40)->default('$420K');
            $table->decimal('sde_growth_percent', 5, 2)->default(10.00);

            $table->unsignedBigInteger('asking_price')->default(1900000)->index();
            $table->string('asking_price_label', 40)->default('$1.9M');
            $table->decimal('sde_multiple', 4, 2)->default(3.20);
            $table->decimal('profit_margin_percent', 5, 2)->default(18.00);
            $table->string('margin_label', 20)->default('18%');

            // Operational & listing flags
            $table->boolean('is_profitable')->default(true);
            $table->boolean('is_owner_operated')->default(true);
            $table->boolean('is_absentee_owner')->default(false);
            $table->boolean('is_growing_market')->default(true);
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_top_match')->default(false);
            $table->string('status', 30)->default('active')->index(); // active | under_loi | sold | draft
            $table->string('discover_tab', 40)->default('high_fit'); // high_fit | new_listings | all
            $table->unsignedTinyInteger('fit_score')->default(88)->index();

            // Images
            $table->string('hero_image', 255)->default('images/business/hero-hvac.png');
            $table->string('dashboard_image', 255)->default('images/dashboard/opp-hvac.png');
            $table->string('watchlist_image', 255)->default('images/watchlist/thumb-hvac.png');
            $table->string('buybox_image', 255)->default('images/buybox/thumb-hvac.png');
            $table->string('facility_image', 255)->default('images/business/facility-video.png');

            // Structured JSON attributes
            $table->json('tags')->nullable();
            $table->json('overview_checklist')->nullable();
            $table->json('why_this_business')->nullable();
            $table->json('criteria_met')->nullable();
            $table->json('fit_criteria')->nullable();

            $table->string('listed_time_ago', 40)->default('2h ago');
            $table->timestamp('listed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedBigInteger('revenue');
            $table->string('revenue_label', 40);
            $table->unsignedBigInteger('sde');
            $table->string('sde_label', 40);
            $table->unsignedTinyInteger('revenue_bar_percent')->default(75);
            $table->unsignedTinyInteger('sde_bar_percent')->default(25);
            $table->timestamps();

            $table->unique(['business_id', 'fiscal_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_financials');
        Schema::dropIfExists('businesses');
    }
};
