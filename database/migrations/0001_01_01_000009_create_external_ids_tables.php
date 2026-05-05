<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_external_ids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained()->cascadeOnDelete();
            $table->string('provider_code', 32);
            $table->string('external_id', 128);
            $table->string('external_url', 500)->nullable();
            $table->json('provider_metadata')->nullable()->comment('provider-specific extras (uri, popularity, etc)');
            $table->timestamp('synced_at')->nullable();

            $table->unique(['provider_code', 'external_id'], 'uk_artist_provider_external');
            $table->unique(['artist_id', 'provider_code'], 'uk_artist_provider');
            $table->foreign('provider_code')->references('code')->on('providers')->restrictOnDelete();
        });

        Schema::create('album_external_ids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('provider_code', 32);
            $table->string('external_id', 128);
            $table->string('external_url', 500)->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->unique(['provider_code', 'external_id'], 'uk_album_provider_external');
            $table->unique(['album_id', 'provider_code'], 'uk_album_provider');
            $table->foreign('provider_code')->references('code')->on('providers')->restrictOnDelete();
        });

        Schema::create('track_external_ids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->string('provider_code', 32);
            $table->string('external_id', 128);
            $table->string('external_url', 500)->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->unique(['provider_code', 'external_id'], 'uk_track_provider_external');
            $table->unique(['track_id', 'provider_code'], 'uk_track_provider');
            $table->foreign('provider_code')->references('code')->on('providers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_external_ids');
        Schema::dropIfExists('album_external_ids');
        Schema::dropIfExists('artist_external_ids');
    }
};
