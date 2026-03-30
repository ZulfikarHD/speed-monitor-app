<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('speed_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->decimal('speed', 5, 2)->comment('Speed in km/h');
            $table->dateTime('recorded_at');
            $table->boolean('is_violation')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['trip_id', 'recorded_at']);
            $table->index('is_violation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speed_logs');
    }
};
