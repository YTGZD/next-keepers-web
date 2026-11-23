<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A photo an admin uploads for a real listing. The path is on the public disk
 * (storage/app/public/listings/...); the thumbnail sits next to it with a
 * "-thumb" suffix. Null means no photo, and the site shows a placeholder.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('businesses', 'photo_path')) {
            return;
        }

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('photo_path', 255)->nullable()->after('facility_image');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('businesses', 'photo_path')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->dropColumn('photo_path');
            });
        }
    }
};
