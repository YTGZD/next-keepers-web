<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real listing data.
 *
 * The original businesses table was shaped around the fictional preview
 * (display labels, page images, a stored fit score). This adds the fields a
 * real listing carries, mirroring the columns of the cleaned listings
 * workbook ("Clean Listings" sheet), so imports can map one to one.
 *
 * Ratios and multiples (price / earnings, margin, cash needed, ...) are not
 * stored: App\Support\ListingMetrics computes them from these raw fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Provenance
            $table->boolean('is_sample')->default(false)->index();
            $table->string('listing_key', 120)->nullable()->unique();   // e.g. bizbuysell:2535967
            $table->string('source', 40)->nullable()->index();           // BizBuySell | BizScout | Acquisition Network | ...
            $table->string('source_url', 500)->nullable();
            $table->string('original_site', 80)->nullable();             // where an aggregator listing came from
            $table->string('original_url', 500)->nullable();
            $table->string('source_status', 40)->nullable();             // Live at source | Not checked | ...
            $table->date('scraped_on')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('published_at')->nullable();

            // Location (most listings are only county or state level)
            $table->string('county', 80)->nullable()->index();
            $table->string('msa', 120)->nullable()->index();
            $table->string('location_precision', 40)->nullable();        // City | County only | Statewide | ...
            $table->string('location_raw', 160)->nullable();
            $table->string('street_address', 160)->nullable();
            $table->string('zip', 10)->nullable();

            // Classification
            $table->string('title_original', 255)->nullable();
            $table->string('listing_type', 40)->nullable();              // Business for Sale | Franchise | Asset Sale | ...
            $table->string('operating_status', 10)->nullable();          // Yes | No | Check
            $table->string('sub_industry', 100)->nullable()->index();
            $table->string('industry_confidence', 10)->nullable();       // High | Medium | Low
            $table->string('brand', 120)->nullable();

            // Financials as listed (whole dollars; null = not disclosed)
            $table->boolean('price_disclosed')->default(true);
            $table->unsignedBigInteger('cash_flow')->nullable();
            $table->unsignedBigInteger('ebitda')->nullable();
            $table->unsignedBigInteger('earnings')->nullable();          // the figure used for multiples
            $table->string('earnings_basis', 60)->nullable();            // SDE | EBITDA | Cash Flow (as listed) | None

            // Assets and lease
            $table->string('real_estate', 40)->nullable();               // Lease only | Owned | ...
            $table->string('real_estate_in_price', 10)->nullable();      // Yes | No | Unknown
            $table->unsignedBigInteger('real_estate_value')->nullable();
            $table->unsignedBigInteger('ffe_value')->nullable();
            $table->string('ffe_in_price', 10)->nullable();
            $table->unsignedBigInteger('inventory_value')->nullable();
            $table->string('inventory_in_price', 10)->nullable();
            $table->unsignedInteger('monthly_rent')->nullable();
            $table->text('lease_notes')->nullable();

            // Operations
            $table->unsignedInteger('employees_full_time')->nullable();
            $table->unsignedInteger('employees_part_time')->nullable();
            $table->unsignedInteger('contractors')->nullable();
            $table->unsignedInteger('square_feet')->nullable();
            $table->decimal('lot_acres', 8, 2)->nullable();

            // Deal terms
            $table->string('broker_name', 160)->nullable();
            $table->string('broker_listing_ref', 80)->nullable();
            $table->string('reason_for_sale', 160)->nullable();
            $table->string('seller_financing', 20)->nullable();
            $table->decimal('seller_financing_percent', 5, 2)->nullable();
            $table->boolean('sba_mentioned')->default(false);
            $table->text('finance_signals')->nullable();
            $table->text('signals')->nullable();

            // Data quality (from the cleaning workflow)
            $table->text('number_sources')->nullable();                  // "Where each number came from"
            $table->string('qa_severity', 10)->nullable();
            $table->json('qa_flags')->nullable();
            $table->text('qa_notes')->nullable();
            $table->boolean('needs_review')->default(false)->index();
            $table->text('admin_notes')->nullable();
        });

        // Real listings often lack these, so they must allow "not disclosed".
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('city', 80)->nullable()->change();
            $table->string('location', 120)->nullable()->change();
            $table->string('state', 10)->nullable()->default(null)->change();
            $table->unsignedSmallInteger('established_year')->nullable()->default(null)->change();
            $table->unsignedInteger('employees')->nullable()->default(null)->change();
            $table->unsignedBigInteger('annual_revenue')->nullable()->default(null)->change();
            $table->unsignedBigInteger('sde')->nullable()->default(null)->change();
            $table->unsignedBigInteger('asking_price')->nullable()->default(null)->change();
            // A listing is never verified unless someone verifies it.
            $table->boolean('is_verified')->default(false)->change();
            // New rows start as drafts until reviewed in the admin panel.
            $table->string('status', 30)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique(['listing_key']);
            $table->dropIndex(['is_sample']);
            $table->dropIndex(['source']);
            $table->dropIndex(['county']);
            $table->dropIndex(['msa']);
            $table->dropIndex(['sub_industry']);
            $table->dropIndex(['needs_review']);
            $table->dropColumn([
                'is_sample', 'listing_key', 'source', 'source_url', 'original_site', 'original_url',
                'source_status', 'scraped_on', 'first_seen_at', 'last_seen_at', 'published_at',
                'county', 'msa', 'location_precision', 'location_raw', 'street_address', 'zip',
                'title_original', 'listing_type', 'operating_status', 'sub_industry', 'industry_confidence', 'brand',
                'price_disclosed', 'cash_flow', 'ebitda', 'earnings', 'earnings_basis',
                'real_estate', 'real_estate_in_price', 'real_estate_value', 'ffe_value', 'ffe_in_price',
                'inventory_value', 'inventory_in_price', 'monthly_rent', 'lease_notes',
                'employees_full_time', 'employees_part_time', 'contractors', 'square_feet', 'lot_acres',
                'broker_name', 'broker_listing_ref', 'reason_for_sale', 'seller_financing',
                'seller_financing_percent', 'sba_mentioned', 'finance_signals', 'signals',
                'number_sources', 'qa_severity', 'qa_flags', 'qa_notes', 'needs_review', 'admin_notes',
            ]);
        });
    }
};
