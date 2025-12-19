<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel locations
 * 
 * Tabel ini menyimpan data lokasi virtual tempat RFID reader "berada"
 * Dalam sistem simulasi, lokasi ditentukan secara virtual, bukan hardware
 * 
 * @author VMS Development Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel locations dengan kolom:
     * - id: Primary key auto increment
     * - name: Nama lokasi (misal: Dekanat, Aula)
     * - code: Kode unik lokasi (misal: DEK, AULA)
     * - description: Deskripsi lengkap lokasi
     * - floor: Lantai lokasi berada
     * - building: Gedung lokasi berada
     * - capacity: Kapasitas maksimal pengunjung
     * - is_active: Status aktif/nonaktif
     * - requires_special_access: Butuh izin khusus atau tidak
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Data Lokasi
            $table->string('name')->unique() // Nama lokasi harus unik
                  ->comment('Nama lokasi lengkap (contoh: Ruang Dekanat)');
            $table->string('code', 20)->unique() // Kode lokasi harus unik
                  ->comment('Kode singkat lokasi (contoh: DEK, AULA, LAB-IT)');
            $table->text('description')->nullable()
                  ->comment('Deskripsi detail lokasi');
            
            // Detail Lokasi Fisik
            $table->string('floor', 20)->nullable()
                  ->comment('Lantai lokasi berada (contoh: Lt. 1, Lt. 2)');
            $table->string('building', 100)->nullable()
                  ->comment('Nama gedung (contoh: Gedung A, Gedung Rektorat)');
            
            // Kapasitas
            $table->integer('capacity')->nullable()
                  ->comment('Kapasitas maksimal pengunjung di lokasi ini');
            
            // Keamanan
            $table->boolean('requires_special_access')
                  ->default(false)
                  ->comment('Apakah lokasi membutuhkan izin khusus: 1=ya, 0=tidak');
            
            // Status
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('Status aktif lokasi: 1=aktif, 0=nonaktif');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft delete
            
            // Indexes
            $table->index('code'); // Index untuk pencarian by code
            $table->index('is_active'); // Index untuk filter aktif/nonaktif
            $table->index('building'); // Index untuk group by gedung
        });
        
        // Tambahkan comment pada tabel
        DB::statement("ALTER TABLE locations COMMENT 'Tabel lokasi virtual untuk simulasi multi-lokasi RFID'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
