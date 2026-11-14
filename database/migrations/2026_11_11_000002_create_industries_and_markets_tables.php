<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('category_key', 40)->index(); // hvac | healthcare | manufacturing | services | retail | construction
            $table->string('icon', 40)->default('briefcase');
            $table->string('color', 30)->default('emerald');
            $table->decimal('market_share_percent', 5, 2)->default(10.00);
            $table->decimal('annual_growth_percent', 5, 2)->default(5.00);
            $table->decimal('avg_multiple', 4, 2)->default(3.50);
            $table->boolean('is_top_industry')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('regional_markets', function (Blueprint $table) {
            $table->id();
            $table->string('city', 80);
            $table->string('state', 60);
            $table->string('state_code', 10)->index();
            $table->string('location_label', 120)->unique(); // e.g. "Rochester, NY"
            $table->string('region_name', 60)->default('Northeast');
            $table->unsignedInteger('service_radius_miles')->default(50);
            $table->unsignedInteger('total_businesses')->default(1000);
            $table->unsignedInteger('opportunities_count')->default(50);
            $table->decimal('population_growth_percent', 5, 2)->default(2.10);
            $table->decimal('housing_permits_growth_percent', 5, 2)->default(12.30);
            $table->decimal('industry_growth_percent', 5, 2)->default(3.50);
            $table->decimal('business_activity_growth_percent', 5, 2)->default(6.80);
            $table->decimal('sba_lending_growth_percent', 5, 2)->default(6.10);
            $table->unsignedBigInteger('avg_asking_price')->default(1300000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regional_markets');
        Schema::dropIfExists('industries');
    }
};
