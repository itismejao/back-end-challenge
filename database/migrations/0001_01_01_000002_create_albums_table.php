<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('album_type', ['album', 'single', 'compilation', 'ep'])->default('album');
            $table->date('release_date')->nullable();
            $table->enum('release_date_precision', ['day', 'month', 'year'])->default('day');
            $table->unsignedSmallInteger('total_tracks')->default(0);
            $table->json('images')->nullable()->comment('normalized: [{url,width,height}]');
            $table->string('upc', 20)->nullable()->comment('industry standard album barcode');
            $table->timestamps();

            $table->index('release_date');
            $table->index('name');
            $table->index('upc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
