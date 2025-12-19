<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel users
 * 
 * Tabel ini menyimpan data pengguna sistem (tamu dan pegawai Unhan)
 * 
 * @author VMS Development Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel users dengan kolom:
     * - id: Primary key auto increment
     * - name: Nama lengkap pengguna
     * - email: Email unik untuk identifikasi
     * - phone: Nomor telepon
     * - user_type: Tipe pengguna (guest/employee)
     * - address: Alamat lengkap
     * - institution: Institusi/instansi asal (untuk tamu)
     * - employee_id: NIP/NIK pegawai (untuk pegawai)
     * - photo: Path foto pengguna
     * - is_active: Status aktif pengguna
     * - timestamps: created_at, updated_at otomatis
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Data Identitas
            $table->string('name'); // Nama lengkap
            $table->string('email')->unique(); // Email unik
            $table->string('phone', 20)->nullable(); // Nomor telepon
            
            // Tipe Pengguna
            $table->enum('user_type', ['guest', 'employee'])
                  ->default('guest')
                  ->comment('Tipe pengguna: guest=tamu, employee=pegawai');
            
            // Data Tambahan
            $table->text('address')->nullable(); // Alamat lengkap
            $table->string('institution')->nullable() // Institusi asal (untuk tamu)
                  ->comment('Nama institusi/instansi asal tamu');
            $table->string('employee_id', 50)->nullable()->unique() // NIP/NIK pegawai
                  ->comment('Nomor Induk Pegawai untuk tipe employee');
            
            // Foto
            $table->string('photo')->nullable() // Path ke file foto
                  ->comment('Path foto pengguna di storage');
            
            // Status
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Status aktif pengguna: 1=aktif, 0=nonaktif');
            
            // Timestamps
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at untuk soft delete
            
            // Indexes untuk optimasi query
            $table->index('email'); // Index untuk pencarian by email
            $table->index('user_type'); // Index untuk filter by type
            $table->index('employee_id'); // Index untuk pegawai
            $table->index('is_active'); // Index untuk filter aktif/nonaktif
        });
        
        // Tambahkan comment pada tabel
        DB::statement("ALTER TABLE users COMMENT 'Tabel master pengguna sistem VMS (tamu dan pegawai)'");
    }

    /**
     * Reverse the migrations.
     * 
     * Menghapus tabel users
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
