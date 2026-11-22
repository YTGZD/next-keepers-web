<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per listings import (an upload in the admin panel or a run of
 * `php artisan nk:import-listings`), so there is a record of what was loaded,
 * from which file, with which filters, and what happened to each row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('via', 20)->default('panel');            // panel | command
            $table->string('file_name', 255);
            $table->string('stored_path', 500);
            $table->json('filters')->nullable();                     // {"preset": ..., "state": [...], "county": [...], ...}
            $table->boolean('dry_run')->default(false);
            $table->string('status', 20)->default('queued');        // queued | running | done | failed
            $table->unsignedInteger('rows_read')->default(0);
            $table->unsignedInteger('rows_matched')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('unchanged_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->json('problems')->nullable();                    // first few row-level problems
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('listing_import_id')->nullable()->after('listing_key')
                ->constrained('listing_imports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('listing_import_id');
        });
        Schema::dropIfExists('listing_imports');
    }
};
