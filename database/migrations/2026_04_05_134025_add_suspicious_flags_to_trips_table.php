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
        Schema::table('trips', function (Blueprint $table) {
            // Suspicious behavior flags for supervisor review
            $table->boolean('is_suspicious')->default(false)->after('synced_at');
            $table->json('suspicious_reasons')->nullable()->after('is_suspicious');
            
            // Additional metrics for pattern analysis
            $table->integer('trip_count_today')->default(0)->after('suspicious_reasons');
            $table->timestamp('flagged_at')->nullable()->after('trip_count_today');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'is_suspicious',
                'suspicious_reasons',
                'trip_count_today',
                'flagged_at',
            ]);
        });
    }
};
