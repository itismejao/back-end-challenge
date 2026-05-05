<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->char('isrc', 12)->nullable()->unique()->comment('industry standard track identifier');
            $table->string('name');
            $table->unsignedInteger('duration_ms');
            $table->boolean('explicit')->default(false);
            $table->unsignedTinyInteger('disc_number')->default(1);
            $table->unsignedSmallInteger('track_number');
            $table->enum('availability_mode', ['global', 'markets', 'unknown'])->default('unknown');
            $table->timestamps();

            $table->index(['album_id', 'disc_number', 'track_number'], 'idx_album_position');
            $table->index('name');
            $table->index('availability_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
