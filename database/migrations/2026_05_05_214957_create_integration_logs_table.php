<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider_code', 32);
            $table->char('isrc', 12);
            $table->enum('status', ['pending', 'success', 'not_found', 'failed'])->default('pending');
            $table->unsignedInteger('duration_ms')->nullable();
            $table->foreignId('track_id')->nullable()->constrained()->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->string('error_class')->nullable();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->json('markets')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('provider_code');
            $table->index('isrc');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
