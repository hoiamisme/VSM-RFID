<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk menambahkan fitur face recognition
 * 
 * Menambahkan kolom untuk menyimpan face descriptor dan konfigurasi
 * 
 * @author VMS Development Team
 * @version 2.0 - Face Recognition Feature
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Face descriptor (128D float array stored as JSON)
            $table->text('face_descriptor')->nullable()->after('photo')
                  ->comment('128D face encoding vector dari face-api.js');
            
            // Timestamp kapan wajah didaftarkan
            $table->timestamp('face_registered_at')->nullable()->after('face_descriptor')
                  ->comment('Waktu saat wajah pertama kali didaftarkan');
            
            // Flag apakah user wajib verifikasi wajah
            $table->boolean('require_face_verification')->default(false)->after('face_registered_at')
                  ->comment('TRUE jika user harus verifikasi wajah saat scan RFID');
            
            // Index untuk query
            $table->index('require_face_verification');
        });
        
        // Tambahkan kolom di tracking_logs untuk audit face verification
        Schema::table('tracking_logs', function (Blueprint $table) {
            // Apakah face verified saat scan ini
            $table->boolean('face_verified')->nullable()->after('status')
                  ->comment('TRUE jika face verification berhasil, FALSE jika gagal, NULL jika tidak pakai face');
            
            // Similarity score face verification
            $table->decimal('face_similarity', 5, 2)->nullable()->after('face_verified')
                  ->comment('Skor kesamaan wajah 0.00-1.00 (0-100%)');
            
            // Method verifikasi yang digunakan
            $table->enum('verification_method', ['rfid_only', 'rfid_face'])
                  ->default('rfid_only')->after('face_similarity')
                  ->comment('Metode verifikasi: rfid_only atau rfid_face (dual auth)');
            
            // Index untuk reporting
            $table->index('face_verified');
            $table->index('verification_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['require_face_verification']);
            $table->dropColumn(['face_descriptor', 'face_registered_at', 'require_face_verification']);
        });
        
        Schema::table('tracking_logs', function (Blueprint $table) {
            $table->dropIndex(['face_verified']);
            $table->dropIndex(['verification_method']);
            $table->dropColumn(['face_verified', 'face_similarity', 'verification_method']);
        });
    }
};
