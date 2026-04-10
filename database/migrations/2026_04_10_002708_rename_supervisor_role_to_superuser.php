<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename 'supervisor' role to 'superuser' in users table.
     *
     * WHY: Must alter column constraint first to allow new value, then update rows.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'supervisor', 'superuser', 'admin'])->default('employee')->change();
        });

        DB::table('users')->where('role', 'supervisor')->update(['role' => 'superuser']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'superuser', 'admin'])->default('employee')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'supervisor', 'superuser', 'admin'])->default('employee')->change();
        });

        DB::table('users')->where('role', 'superuser')->update(['role' => 'supervisor']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['employee', 'supervisor', 'admin'])->default('employee')->change();
        });
    }
};
