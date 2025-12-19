<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel access_rights
 * 
 * Tabel ini menyimpan hak akses user ke lokasi tertentu
 * Digunakan untuk validasi apakah user boleh masuk ke suatu lokasi
 * 
 * @author VMS Development Team
 * @version 1.0
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel access_rights dengan kolom:
     * - id: Primary key auto increment
     * - user_id: Foreign key ke tabel users
     * - location_id: Foreign key ke tabel locations
     * - can_access: Boolean izin akses
     * - access_type: Tipe akses (permanent/temporary/scheduled)
     * - valid_from: Mulai berlaku
     * - valid_until: Sampai berlaku
     * - time_restrictions: Batasan waktu akses (JSON)
     */
    public function up(): void
    {
        Schema::create('access_rights', function (Blueprint $table) {
            // Primary Key
            $table->id();
            
            // Relasi ke User
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('ID user yang memiliki hak akses');
            
            // Relasi ke Location
            $table->foreignId('location_id')
                  ->constrained('locations')
                  ->onDelete('cascade')
                  ->comment('ID lokasi yang dapat diakses');
            
            // Status Akses
            $table->boolean('can_access')
                  ->default(true)
                  ->comment('Izin akses: 1=boleh masuk, 0=tidak boleh masuk');
            
            // Tipe Akses
            $table->enum('access_type', ['permanent', 'temporary', 'scheduled'])
                  ->default('permanent')
                  ->comment('Tipe akses: permanent=tetap, temporary=sementara, scheduled=terjadwal');
            
            // Periode Berlaku
            $table->timestamp('valid_from')->nullable()
                  ->comment('Tanggal mulai berlaku hak akses');
            $table->timestamp('valid_until')->nullable()
                  ->comment('Tanggal berakhir hak akses (null = selamanya)');
            
            // Batasan Waktu (JSON)
            // Format: {"days": ["monday", "tuesday"], "hours": {"start": "08:00", "end": "17:00"}}
            $table->json('time_restrictions')->nullable()
                  ->comment('Batasan waktu akses dalam format JSON');
            
            // Alasan Pemberian Akses
            $table->text('reason')->nullable()
                  ->comment('Alasan pemberian atau pencabutan hak akses');
            
            // Granted By (Siapa yang memberikan akses)
            $table->foreignId('granted_by')->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('ID user yang memberikan hak akses ini');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Soft delete
            
            // Composite Unique Index
            // Satu user hanya bisa punya satu record akses per lokasi
            $table->unique(['user_id', 'location_id']);
            
            // Indexes
            $table->index('can_access'); // Index untuk filter allowed/denied
            $table->index('access_type'); // Index untuk group by type
            $table->index(['valid_from', 'valid_until']); // Index untuk cek periode
        });
        
        // Tambahkan comment pada tabel
        DB::statement("ALTER TABLE access_rights COMMENT 'Tabel hak akses user ke lokasi tertentu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_rights');
    }
};
