<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel rfid_cards
 * 
 * Tabel ini menyimpan data kartu RFID dan relasinya dengan user
 * UID dari RFID reader akan disimpan di tabel ini
 * 
 * @author VMS Development Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel rfid_cards dengan kolom:
     * - id: Primary key auto increment
     * - uid: UID unik dari kartu RFID (contoh: E004012345ABCD)
     * - user_id: Foreign key ke tabel users
     * - card_number: Nomor kartu fisik (opsional)
     * - status: Status kartu (active/inactive/blocked/lost)
     * - registered_at: Tanggal registrasi kartu
     * - expired_at: Tanggal kadaluarsa kartu
     * - last_used_at: Terakhir kali kartu digunakan
     */
    public function up(): void
    {
        Schema::create('rfid_cards', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // UID Kartu RFID
            $table->string('uid', 100)->unique()
                  ->comment('UID unik dari kartu RFID (dibaca oleh reader)');
            
            // Relasi ke User
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade') // Jika user dihapus, kartu ikut terhapus
                  ->comment('ID user pemilik kartu');
            
            // Nomor Kartu Fisik
            $table->string('card_number', 50)->nullable()
                  ->comment('Nomor kartu yang tercetak (jika ada)');
            
            // Status Kartu
            $table->enum('status', ['active', 'inactive', 'blocked', 'lost'])
                  ->default('active')
                  ->comment('Status kartu: active=aktif, inactive=nonaktif, blocked=diblokir, lost=hilang');
            
            // Tanggal Registrasi
            $table->timestamp('registered_at')
                  ->useCurrent()
                  ->comment('Tanggal dan waktu kartu didaftarkan');
            
            // Tanggal Kadaluarsa
            $table->timestamp('expired_at')->nullable()
                  ->comment('Tanggal kadaluarsa kartu (untuk tamu temporary)');
            
            // Terakhir Digunakan
            $table->timestamp('last_used_at')->nullable()
                  ->comment('Terakhir kali kartu digunakan untuk scan');
            
            // Catatan
            $table->text('notes')->nullable()
                  ->comment('Catatan tambahan tentang kartu');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft delete
            
            // Indexes untuk optimasi
            $table->index('uid'); // Index untuk pencarian by UID
            $table->index('user_id'); // Index untuk pencarian by user
            $table->index('status'); // Index untuk filter by status
            $table->index('expired_at'); // Index untuk cek kadaluarsa
            $table->index('last_used_at'); // Index untuk sorting
        });
        
        // Tambahkan comment pada tabel
        DB::statement("ALTER TABLE rfid_cards COMMENT 'Tabel kartu RFID dan relasinya dengan user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_cards');
    }
};
