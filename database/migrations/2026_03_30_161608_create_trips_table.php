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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'auto_stopped'])->default('in_progress');
            $table->decimal('total_distance', 10, 2)->nullable()->comment('Distance in kilometers');
            $table->decimal('max_speed', 5, 2)->nullable()->comment('Speed in km/h');
            $table->decimal('average_speed', 5, 2)->nullable()->comment('Speed in km/h');
            $table->integer('violation_count')->default(0);
            $table->integer('duration_seconds')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('synced_at')->nullable()->comment('When offline data was synced');
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index('status');
            $table->index('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
