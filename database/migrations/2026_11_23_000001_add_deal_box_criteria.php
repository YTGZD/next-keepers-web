<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The deal box: the buyer's criteria panel on /buy-box.
 *
 * buy_boxes keeps one active set of criteria per account (what the dashboard
 * matches on). deal_boxes holds the named boxes a buyer saves ("First SBA
 * deal"); saving one also makes it the active buy box. Both are private to
 * the account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buy_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('max_price_ex_re')->nullable();         // asking price without real estate
            $table->decimal('max_multiple', 5, 2)->nullable();                 // price (ex real estate) / earnings
            $table->unsignedSmallInteger('min_years_in_business')->nullable();
            $table->json('sources')->nullable();
            $table->string('real_estate_pref', 20)->nullable();                // leased | included
            $table->string('keyword', 120)->nullable();
            $table->boolean('require_earnings')->default(false);
            $table->boolean('seller_financing_only')->default(false);
            // Data hygiene, on by default: these hide rows, they don't score them.
            $table->boolean('operating_only')->default(true);
            $table->boolean('hide_delisted')->default(true);
            $table->boolean('hide_out_of_county')->default(true);
        });

        Schema::create('deal_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->json('criteria');
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_boxes');
        Schema::table('buy_boxes', function (Blueprint $table) {
            $table->dropColumn([
                'max_price_ex_re', 'max_multiple', 'min_years_in_business', 'sources', 'real_estate_pref', 'keyword',
                'require_earnings', 'seller_financing_only', 'operating_only', 'hide_delisted', 'hide_out_of_county',
            ]);
        });
    }
};
