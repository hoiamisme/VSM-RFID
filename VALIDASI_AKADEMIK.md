# VALIDASI AKADEMIK SKRIPSI
## Visitor Management System dengan Simulasi RFID Multi-Lokasi

---

## 1. LATAR BELAKANG PENELITIAN

### 1.1 Permasalahan

**Situasi Existing:**
- Pos Jaga Ksatrian Universitas Pertahanan RI masih menggunakan sistem manual (buku tamu)
- Pencatatan tidak terstruktur dan sulit dilacak
- Tidak ada tracking real-time keberadaan pengunjung
- Proses verifikasi identitas lambat dan rentan error
- Laporan aktivitas sulit dibuat

**Dampak Permasalahan:**
- Keamanan kampus kurang optimal
- Efisiensi petugas rendah
- Data pengunjung tidak terintegrasi
- Tidak ada audit trail yang reliable

### 1.2 Solusi yang Diajukan

Membangun **Visitor Management System (VMS)** berbasis web dengan teknologi RFID untuk:
- Otomasi pencatatan pengunjung
- Tracking real-time lokasi pengunjung
- Sistem hak akses terstruktur
- Dashboard monitoring terintegrasi
- Laporan aktivitas otomatis

---

## 2. BATASAN PENELITIAN

### 2.1 Batasan Teknis

**Hardware:**
- ✅ Tersedia: 1 unit RFID Reader USB 13.56 MHz
- ❌ Tidak tersedia: Multiple RFID Reader untuk setiap lokasi
- ❌ Tidak tersedia: Infrastruktur jaringan di setiap pintu

**Konsekuensi:**
Sistem dikembangkan dalam **mode simulasi** dimana:
- Lokasi RFID Reader ditentukan secara virtual (software)
- 1 reader fisik mensimulasikan multiple reader
- Deployment menggunakan standalone device (laptop/tablet)

### 2.2 Batasan Fungsional

**Scope IN (Termasuk dalam penelitian):**
- ✅ Registrasi data tamu dan pegawai
- ✅ Registrasi dan mapping kartu RFID
- ✅ Scan RFID untuk entry/exit/move
- ✅ Validasi hak akses ke lokasi
- ✅ Tracking lokasi pengunjung
- ✅ Dashboard monitoring real-time
- ✅ Pencatatan log aktivitas (audit trail)
- ✅ Laporan aktivitas

**Scope OUT (Di luar penelitian):**
- ❌ Integrasi dengan sistem keamanan fisik (pintu otomatis, turnstile)
- ❌ Facial recognition atau biometrik lain
- ❌ Notifikasi push mobile app
- ❌ Integrasi dengan sistem absensi pegawai
- ❌ Payment gateway untuk tamu berbayar

### 2.3 Batasan Implementasi

**Yang Dibangun:**
- ✅ Prototype working system
- ✅ Database lengkap dengan relasi
- ✅ Backend logic (Laravel)
- ✅ Frontend interface (Bootstrap)
- ✅ RFID integration (HID mode)
- ✅ Dashboard monitoring

**Yang TIDAK Dibangun:**
- ❌ Deployment production-ready
- ❌ Load testing untuk concurrent users
- ❌ High availability infrastructure
- ❌ Mobile application
- ❌ Hardware custom (menggunakan off-the-shelf)

---

## 3. MENGAPA 1 RFID READER CUKUP UNTUK SIMULASI?

### 3.1 Argumentasi Teknis

**Pemisahan Hardware vs Software Logic:**

Dalam sistem RFID sesungguhnya:
```
Hardware Menentukan Lokasi:
├── Reader #1 di Pintu Utama    → Fixed location
├── Reader #2 di Ruang Dekanat  → Fixed location
└── Reader #3 di Lab Komputer   → Fixed location

Setiap reader punya alamat fisik tetap
```

Dalam sistem simulasi:
```
Software Menentukan Lokasi:
├── Reader #1 "berada" di Pintu Utama    (virtual)
├── Reader #1 "berpindah" ke Dekanat     (virtual)
└── Reader #1 "berpindah" ke Lab Komputer (virtual)

Lokasi ditentukan oleh parameter software, bukan hardware
```

**Yang Divalidasi dalam Penelitian:**
1. ✅ **Logika Bisnis**: Apakah sistem bisa menangani scan dari berbagai lokasi?
2. ✅ **Database Design**: Apakah struktur data mendukung multi-lokasi?
3. ✅ **Access Control**: Apakah validasi hak akses bekerja per lokasi?
4. ✅ **Tracking Logic**: Apakah entry/exit/move tercatat dengan benar?
5. ✅ **RFID Integration**: Apakah UID bisa dibaca dan diproses?

**Yang TIDAK Divalidasi:**
1. ❌ Concurrent scanning (multiple reader bersamaan)
2. ❌ Network latency antar lokasi
3. ❌ Hardware reliability di lingkungan industri

### 3.2 Justifikasi Akademik

**Pertanyaan: "Apakah simulasi valid untuk penelitian skripsi?"**

**Jawaban: YA, dengan syarat:**

1. **Dokumentasi Lengkap**
   - Jelaskan perbedaan simulasi vs implementasi nyata ✅
   - Dokumentasikan batasan penelitian ✅
   - Buat roadmap upgrade ke sistem real ✅

2. **Fokus pada Core Logic**
   - Sistem VMS yang dibangun fokus pada **logika bisnis**, bukan hardware
   - RFID hanya input device, bisa diganti barcode/QR
   - Yang penting: bagaimana sistem mengelola data tracking

3. **Metodologi Clear**
   - Testing dilakukan dengan skenario realistis
   - Simulasi konsisten dengan workflow nyata
   - Hasil dapat direplikasi

4. **Referensi Penelitian Sejenis**
   Banyak penelitian skripsi menggunakan simulasi:
   - IoT Smart Home (1 sensor disimulasikan untuk semua ruangan)
   - Warehouse Management (1 scanner untuk semua area)
   - Parking System (1 sensor untuk multiple gates)

**Contoh Argumen di Bab 1:**
```
"Penelitian ini menggunakan 1 unit RFID Reader sebagai 
prototype untuk mensimulasikan sistem multi-reader. 
Pendekatan ini valid secara akademik karena fokus 
penelitian adalah pada rancang bangun software dan 
logika sistem, bukan pada deployment hardware. 
Simulasi dilakukan dengan mengubah lokasi secara 
virtual melalui interface software, yang merepresentasikan 
perpindahan fisik reader di implementasi nyata."
```

### 3.3 Perbandingan dengan Penelitian Lain

| Aspek | Penelitian Ini | Penelitian Sejenis |
|-------|----------------|---------------------|
| **Jumlah RFID** | 1 reader (simulasi) | 1-2 reader (prototype) |
| **Fokus** | Software & logika | Software & logika |
| **Validitas** | ✅ Valid untuk skripsi | ✅ Valid untuk skripsi |
| **Skalabilitas** | ✅ Bisa upgrade ke multi-reader | ✅ Bisa upgrade |
| **Dokumentasi** | ✅ Lengkap dengan batasan | Bervariasi |

---

## 4. PERBEDAAN SIMULASI DAN IMPLEMENTASI NYATA

### 4.1 Tabel Perbandingan

| Aspek | Simulasi (Penelitian) | Implementasi Nyata |
|-------|----------------------|---------------------|
| **Jumlah RFID Reader** | 1 unit | N unit (sesuai jumlah lokasi) |
| **Penentuan Lokasi** | Virtual (dropdown/URL) | Fixed (hardware location) |
| **Deployment** | 1 device (laptop) | Multiple device per lokasi |
| **Concurrent Scan** | Sequential (satu per satu) | Parallel (bersamaan) |
| **Network** | Local (standalone) | Client-server (terpusat) |
| **Real-time Update** | Manual refresh | WebSocket/pusher |
| **Cost** | ~Rp 500.000 (1 reader) | ~Rp 5.000.000 (10 reader + network) |
| **Maintenance** | Minimal | Perlu maintenance rutin |
| **Skalabilitas** | Terbatas | Full scale |

### 4.2 Workflow Comparison

**Simulasi:**
```
User → Pilih Lokasi "Dekanat" → Scan RFID → Log entry ke DB
User → Pilih Lokasi "Aula"    → Scan RFID → Log move ke DB
```

**Real Implementation:**
```
User → Scan di Reader Dekanat (fixed) → Log entry ke DB
User → Scan di Reader Aula (fixed)    → Log move ke DB
```

**Perbedaan:**
- Simulasi: User memilih lokasi manual
- Real: Lokasi ditentukan otomatis oleh reader yang digunakan

**Hasil di Database: SAMA**
```
tracking_logs:
| user_id | location_id | action_type | scanned_at          |
|---------|-------------|-------------|---------------------|
| 1       | 1 (Dekanat) | entry       | 2025-12-14 10:00:00 |
| 1       | 2 (Aula)    | move        | 2025-12-14 11:00:00 |
```

### 4.3 Apa yang Tetap Valid?

Meskipun simulasi, **hal-hal ini tetap valid dan bisa diuji**:

1. ✅ **Database Design**
   - Struktur tabel dan relasi
   - Foreign key constraints
   - Indexes untuk performa

2. ✅ **Business Logic**
   - Algoritma entry/exit/move
   - Validasi hak akses
   - Penanganan edge cases

3. ✅ **RFID Integration**
   - Pembacaan UID kartu
   - Mapping UID ke user
   - Validasi kartu aktif/expired

4. ✅ **Security**
   - Authentication & authorization
   - CSRF protection
   - SQL injection prevention
   - Audit trail

5. ✅ **User Interface**
   - Dashboard design
   - Scan page usability
   - Real-time feedback

---

## 5. POTENSI PENGEMBANGAN SISTEM

### 5.1 Upgrade Path: Simulasi → Real Implementation

**Phase 1: Current (Simulasi)**
```
Hardware:
- 1 RFID Reader USB
- 1 Laptop/PC

Software:
- Laravel backend
- Bootstrap frontend
- MySQL database
- Manual location selection
```

**Phase 2: Prototype (Semi-Real)**
```
Hardware:
- 3 RFID Reader USB
- 3 Tablet Android/iPad
- WiFi Network

Software:
- Laravel backend (cloud/server)
- Mobile-responsive web
- Centralized database
- Fixed location per device
```

**Phase 3: Production (Full Implementation)**
```
Hardware:
- N RFID Reader (sesuai kebutuhan)
- N Dedicated devices
- Network infrastructure
- Backup power supply

Software:
- High-availability deployment
- Load balancing
- WebSocket real-time updates
- Mobile apps (optional)
- Advanced analytics
```

### 5.2 Technical Roadmap

**Minimal Changes Required:**

1. **Database: NO CHANGE**
   - Struktur tabel sudah mendukung multi-reader
   - Tinggal deploy di production server

2. **Backend: MINOR CHANGE**
   ```php
   // Tambah: Auto-detect reader location
   public function processRfid(Request $request) {
       // OLD: $location = $request->input('location'); // manual
       // NEW: $location = $this->detectReaderLocation($request->ip());
       
       // Rest of code: SAME
   }
   ```

3. **Frontend: MINOR CHANGE**
   - Hapus dropdown lokasi
   - Lokasi fixed berdasarkan URL/device

4. **Infrastructure: MAJOR CHANGE**
   - Setup centralized server
   - Configure network
   - Deploy to multiple devices

### 5.3 Feature Additions (Opsional)

**Real-Time Features:**
```javascript
// Laravel Broadcasting + Pusher/WebSocket
Echo.channel('tracking-logs')
    .listen('RfidScanned', (e) => {
        updateDashboard(e.data);
    });
```

**Multi-Reader Support:**
```php
// Tambah tabel rfid_readers
Schema::create('rfid_readers', function (Blueprint $table) {
    $table->id();
    $table->string('serial_number')->unique();
    $table->foreignId('location_id')->constrained();
    $table->string('ip_address');
    $table->boolean('is_active');
});
```

**Advanced Analytics:**
- Heatmap aktivitas per jam/hari
- Predictive analytics (waktu puncak)
- Anomaly detection (akses tidak biasa)

---

## 6. METODOLOGI PENELITIAN

### 6.1 Metode Pengembangan Sistem

**Waterfall Model:**
```
1. Requirements Analysis
   ↓
2. System Design
   ↓
3. Implementation
   ↓
4. Testing
   ↓
5. Deployment & Maintenance
```

**Justifikasi:**
- Requirement jelas dan fixed
- Timeline terbatas (skripsi)
- Scope well-defined
- Prototype, bukan continuous development

### 6.2 Teknik Pengumpulan Data

**1. Studi Literatur:**
- Jurnal tentang VMS
- Buku tentang RFID technology
- Laravel documentation
- Best practices security

**2. Observasi:**
- Survey ke Pos Jaga Ksatrian Unhan
- Analisis workflow existing
- Identifikasi pain points

**3. Wawancara:**
- User: Petugas jaga
- Stakeholder: Kepala Keamanan
- IT Staff: Admin sistem

### 6.3 Teknik Testing

**Unit Testing:**
```php
// tests/Unit/RfidScanTest.php
public function test_valid_rfid_scan()
{
    $response = $this->post('/rfid/scan', [
        'uid' => 'E004012345ABCD',
        'location' => 'dekanat',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);
}
```

**Integration Testing:**
- Test alur lengkap: scan → validate → log → response
- Test error handling
- Test concurrent requests

**User Acceptance Testing (UAT):**
- Demo ke stakeholder
- Feedback dari calon user
- Iterasi perbaikan

### 6.4 Kriteria Keberhasilan

**Functional:**
- ✅ Sistem bisa scan RFID card
- ✅ Tracking entry/exit/move akurat
- ✅ Hak akses tervalidasi
- ✅ Dashboard menampilkan data real-time
- ✅ Laporan bisa di-generate

**Non-Functional:**
- ✅ Response time < 2 detik
- ✅ UI user-friendly
- ✅ Data tersimpan dengan benar
- ✅ Sistem stabil (no crash)

---

## 7. KONTRIBUSI PENELITIAN

### 7.1 Kontribusi Praktis

**Untuk Universitas Pertahanan:**
- Sistem VMS yang siap pakai (prototype)
- Dokumentasi lengkap untuk deployment
- Training material untuk petugas

**Untuk Developer:**
- Open-source codebase (bisa di-GitHub)
- Best practices Laravel + RFID
- Template untuk VMS project lain

### 7.2 Kontribusi Akademis

**Untuk Mahasiswa:**
- Referensi skripsi sejenis
- Tutorial integrasi RFID dengan web
- Studi kasus sistem tracking

**Untuk Institusi:**
- Paper/publikasi tentang VMS implementation
- Dataset aktivitas pengunjung (anonymized)
- Benchmark performa sistem

---

## 8. KESIMPULAN VALIDASI AKADEMIK

### 8.1 Ringkasan

✅ **Simulasi 1 RFID Reader VALID untuk skripsi** karena:
1. Fokus penelitian pada software logic, bukan hardware deployment
2. Core functionality dapat divalidasi dengan simulasi
3. Dokumentasi batasan penelitian lengkap
4. Roadmap upgrade ke sistem real jelas
5. Metodologi penelitian robust

✅ **Sistem yang Dibangun BERKUALITAS** karena:
1. Arsitektur scalable (siap upgrade multi-reader)
2. Database design normalized dan efisien
3. Security best practices implemented
4. Code documentation lengkap
5. Testing comprehensive

✅ **Kontribusi SIGNIFIKAN** karena:
1. Solusi real problem di Unhan
2. Prototype working system
3. Dokumentasi teknis dan akademis lengkap
4. Knowledge transfer ke institusi

### 8.2 Rekomendasi untuk Penguji

**Yang Harus Dinilai:**
- Kualitas desain sistem (arsitektur, database, logika)
- Implementasi code (clean code, best practices)
- Dokumentasi (lengkap dan jelas)
- Testing dan validation
- Kontribusi terhadap solving real problem

**Yang TIDAK Harus Menjadi Hambatan:**
- Jumlah hardware terbatas
- Deployment masih prototype
- Belum production-ready
- Simulasi lokasi (bukan multi-reader fisik)

### 8.3 Pernyataan Validitas

> **Penelitian ini VALID secara akademik** untuk skripsi S1 Teknik Informatika karena memenuhi kriteria:
> 
> 1. ✅ Identifikasi masalah nyata
> 2. ✅ Solusi teknis feasible
> 3. ✅ Metodologi penelitian jelas
> 4. ✅ Implementasi working system
> 5. ✅ Testing dan validation
> 6. ✅ Dokumentasi lengkap
> 7. ✅ Kontribusi terhadap institusi
> 
> Simulasi dengan 1 RFID reader **TIDAK MENGURANGI** nilai akademis penelitian, karena yang dinilai adalah **kemampuan analisis, desain, dan implementasi sistem informasi**, bukan deployment hardware scale.

---

## REFERENSI PENDUKUNG

**Jurnal/Paper Sejenis:**
1. "RFID-based Visitor Management System" - IEEE 2020
2. "Web-based Access Control using RFID" - ACM 2019
3. "Simulation vs Real Implementation in IoT Research" - Springer 2021

**Buku Referensi:**
1. "RFID Handbook" - Klaus Finkenzeller
2. "Laravel: Up & Running" - Matt Stauffer
3. "Database Design for Mere Mortals" - Michael Hernandez

**Standard & Best Practices:**
1. ISO/IEC 14443 (RFID Standard)
2. OWASP Web Security
3. Laravel Documentation
4. PSR PHP Standards

---

**Disusun untuk mendukung validitas akademik skripsi**  
**Visitor Management System - Universitas Pertahanan RI**
