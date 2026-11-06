<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('early_access_signups', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name', 120)->nullable();
            $table->string('interest', 20)->default('buyer'); // buyer | owner | investor
            $table->string('source', 80)->nullable();          // utm_source, when present
            $table->string('ip_hash', 64)->nullable();         // salted hash, never the raw IP
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('consented_at');
            $table->timestamp('invited_at')->nullable();       // set when you send the early-access invite
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_access_signups');
    }
};
