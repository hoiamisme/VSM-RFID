<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel tracking_logs
 * 
 * Tabel ini mencatat semua aktivitas scan RFID
 * Setiap scan (berhasil atau gagal) akan tercatat di sini
 * 
 * Logika Action Type:
 * - entry: Scan pertama di lokasi (masuk)
 * - exit: Scan kedua di lokasi yang sama (keluar)
 * - move: Scan di lokasi berbeda (pindah lokasi)
 * - denied: Scan ditolak (tidak punya akses)
 * 
 * @author VMS Development Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracking_logs', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Relasi ke RFID Card
            $table->foreignId('rfid_card_id')
                  ->constrained('rfid_cards')
                  ->onDelete('cascade')
                  ->comment('ID kartu RFID yang di-scan');
            
            // Relasi ke User
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('ID user pemilik kartu');
            
            // Relasi ke Location
            $table->foreignId('location_id')
                  ->constrained('locations')
                  ->onDelete('cascade')
                  ->comment('ID lokasi saat scan dilakukan');
            
            // Tipe Aksi
            $table->enum('action_type', ['entry', 'exit', 'move', 'denied'])
                  ->comment('Tipe aksi: entry=masuk, exit=keluar, move=pindah, denied=ditolak');
            
            // Status Scan
            $table->enum('status', ['accepted', 'denied'])
                  ->default('accepted')
                  ->comment('Status scan: accepted=diterima, denied=ditolak');
            
            // Waktu Scan
            $table->timestamp('scanned_at')
                  ->useCurrent()
                  ->comment('Waktu scan dilakukan');
            
            // Data Tambahan
            $table->string('rfid_uid', 100)
                  ->comment('UID RFID yang di-scan (untuk audit)');
            
            $table->string('location_code', 20)
                  ->comment('Kode lokasi saat scan (untuk audit)');
            
            $table->string('ip_address', 45)->nullable()
                  ->comment('IP address device yang melakukan scan');
            
            $table->string('user_agent')->nullable()
                  ->comment('User agent browser yang melakukan scan');
            
            // Alasan Ditolak (jika status = denied)
            $table->string('denial_reason')->nullable()
                  ->comment('Alasan penolakan akses');
            
            // Temperature (optional - untuk screening COVID)
            $table->decimal('temperature', 4, 1)->nullable()
                  ->comment('Suhu tubuh saat scan (opsional)');
            
            // Notes
            $table->text('notes')->nullable()
                  ->comment('Catatan tambahan');
            
            // Timestamps
            $table->timestamps(); // created_at = waktu record dibuat
            
            // Indexes untuk optimasi query
            $table->index('user_id'); // Untuk query by user
            $table->index('location_id'); // Untuk query by location
            $table->index('action_type'); // Untuk filter by action
            $table->index('status'); // Untuk filter accepted/denied
            $table->index('scanned_at'); // Untuk sorting by time
            $table->index(['user_id', 'scanned_at']); // Composite untuk histori user
            $table->index(['location_id', 'scanned_at']); // Composite untuk histori lokasi
        });
        
        // Tambahkan comment pada tabel
        DB::statement("ALTER TABLE tracking_logs COMMENT 'Tabel log tracking semua aktivitas scan RFID'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_logs');
    }
};
