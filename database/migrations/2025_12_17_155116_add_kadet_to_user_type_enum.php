<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support directly modifying ENUM, so we use raw SQL
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('guest', 'employee', 'kadet') NOT NULL DEFAULT 'guest' COMMENT 'Tipe pengguna: guest=tamu, employee=pegawai, kadet=taruna/kadet'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN user_type ENUM('guest', 'employee') NOT NULL DEFAULT 'guest' COMMENT 'Tipe pengguna: guest=tamu, employee=pegawai'");
    }
};
