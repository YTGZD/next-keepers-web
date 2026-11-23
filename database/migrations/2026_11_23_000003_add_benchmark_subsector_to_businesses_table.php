<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The BizBuySell subsector a listing is compared with, when it should differ from the
     * default for its sub-industry (workbook column "BBS Subsector (override)"). Null = default.
     */
    public function up(): void
    {
        if (Schema::hasColumn('businesses', 'benchmark_subsector')) {
            return;
        }

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('benchmark_subsector', 120)->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('businesses', 'benchmark_subsector')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('benchmark_subsector');
            });
        }
    }
};
