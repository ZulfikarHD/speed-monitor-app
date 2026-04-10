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
        Schema::table('users', function (Blueprint $table) {
            $table->string('npk')->nullable()->unique()->after('name');
            $table->string('divisi')->nullable()->after('npk');
            $table->string('departement')->nullable()->after('divisi');
            $table->string('section')->nullable()->after('departement');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['npk', 'divisi', 'departement', 'section']);
        });
    }
};
