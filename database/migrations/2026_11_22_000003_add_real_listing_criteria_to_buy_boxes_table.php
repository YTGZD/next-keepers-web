<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buy box criteria that real listings can be matched on. Real listings are
 * located by state and county (most have no street address), so a radius
 * around a city doesn't apply; figures are optional because many listings
 * don't disclose them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buy_boxes', function (Blueprint $table) {
            $table->json('states')->nullable()->after('industries');
            $table->json('counties')->nullable()->after('states');
            $table->unsignedBigInteger('min_earnings')->nullable()->after('max_sde');
        });

        Schema::table('buy_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('min_revenue')->nullable()->default(null)->change();
            $table->unsignedBigInteger('max_revenue')->nullable()->default(null)->change();
            $table->unsignedBigInteger('min_asking_price')->nullable()->default(null)->change();
            $table->unsignedBigInteger('max_asking_price')->nullable()->default(null)->change();
        });

        // Real accounts created before this got the preview's sample criteria
        // ("HVAC, Restoration, Healthcare in NY + 100 miles"); start them empty.
        $demo = config('nextkeepers.demo_email');
        DB::table('buy_boxes')
            ->whereIn('user_id', DB::table('users')->where('email', '!=', $demo)->select('id'))
            ->where('location', 'New York, NY + 100 miles')
            ->update([
                'industries' => '[]',
                'location' => '',
                'min_revenue' => null, 'max_revenue' => null,
                'min_asking_price' => null, 'max_asking_price' => null,
                'profitable_only' => false, 'growing_market' => false,
            ]);
    }

    public function down(): void
    {
        Schema::table('buy_boxes', function (Blueprint $table) {
            $table->dropColumn(['states', 'counties', 'min_earnings']);
        });
    }
};
