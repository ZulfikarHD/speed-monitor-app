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
            $table->enum('shift_type', ['non_shift', 'shift_pagi', 'shift_malam'])->nullable()->after('notes');
            $table->enum('vehicle_type', ['mobil', 'motor'])->nullable()->after('shift_type');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['shift_type', 'vehicle_type']);
        });
    }
};
