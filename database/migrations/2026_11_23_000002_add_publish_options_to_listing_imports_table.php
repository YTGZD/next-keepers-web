<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Publish on import": an import can publish its new drafts as soon as it
 * finishes, instead of leaving them for review one page at a time.
 * Guarded with hasColumn so the unattended migrate on deploy is safe to repeat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_imports', function (Blueprint $table) {
            if (! Schema::hasColumn('listing_imports', 'publish')) {
                $table->boolean('publish')->default(false)->after('dry_run');
            }
            if (! Schema::hasColumn('listing_imports', 'publish_flagged')) {
                $table->boolean('publish_flagged')->default(false)->after('publish');
            }
            if (! Schema::hasColumn('listing_imports', 'published_count')) {
                $table->unsignedInteger('published_count')->default(0)->after('failed_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listing_imports', function (Blueprint $table) {
            $table->dropColumn(['publish', 'publish_flagged', 'published_count']);
        });
    }
};
