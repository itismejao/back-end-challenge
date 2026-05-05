<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_available_markets', function (Blueprint $table) {
            $table->foreignId('track_id')->constrained()->cascadeOnDelete();
            $table->char('country_code', 2);

            $table->primary(['track_id', 'country_code']);
            $table->foreign('country_code')->references('code')->on('countries')->restrictOnDelete();
            $table->index('country_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_available_markets');
    }
};
