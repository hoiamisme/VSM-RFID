# DOKUMENTASI ARSITEKTUR SISTEM VMS
## Visitor Management System dengan Integrasi RFID Simulasi

---

## 1. ARSITEKTUR MVC LARAVEL

### 1.1 Konsep Dasar MVC

**Model-View-Controller (MVC)** adalah pola arsitektur software yang memisahkan aplikasi menjadi tiga komponen utama:

```
┌─────────────────────────────────────────────────────────┐
│                    ARSITEKTUR MVC                        │
└─────────────────────────────────────────────────────────┘

┌──────────┐         ┌──────────┐         ┌──────────┐
│   VIEW   │◄────────│CONTROLLER│────────►│  MODEL   │
│ (Blade)  │         │ (Logic)  │         │(Eloquent)│
└──────────┘         └──────────┘         └──────────┘
     ▲                     │                     │
     │                     │                     │
     │                     ▼                     ▼
  User UI            Business Logic        Database
```

**Model (M):**
- Mengelola data dan logika bisnis
- Interaksi dengan database melalui Eloquent ORM
- File location: `app/Models/`
- Contoh: `User.php`, `RfidCard.php`, `Location.php`

**View (V):**
- Menampilkan data ke pengguna
- Template Blade Laravel
- File location: `resources/views/`
- Contoh: `scan.blade.php`, `dashboard.blade.php`

**Controller (C):**
- Menerima input dari user
- Memproses logika aplikasi
- Mengirim data ke View
- File location: `app/Http/Controllers/`
- Contoh: `RfidScanController.php`, `DashboardController.php`

---

## 2. ALUR DATA SISTEM VMS

### 2.1 Alur Lengkap: RFID → Browser → Controller → Model → Database

```
┌─────────────────────────────────────────────────────────────────┐
│                    ALUR DATA SISTEM VMS                          │
└─────────────────────────────────────────────────────────────────┘

[1] RFID READER                  [2] BROWSER
    (USB Device)                      (Client)
         │                               │
         │ UID: "ABC12345"              │
         │ (Keyboard Input)              │
         └───────────►┌──────────────┐  │
                      │ Input Field  │  │
                      │ (autofocus)  │  │
                      └──────────────┘  │
                             │           │
                             │ JavaScript│
                             │ detects   │
                             │ Enter key │
                             ▼           │
                      ┌──────────────┐  │
                      │ AJAX/Fetch   │  │
                      │ POST Request │  │
                      └──────────────┘  │
                             │           │
                             │ HTTP POST │
                             ▼           │
                      ┌──────────────┐
                      │   ROUTING    │
                      │  (web.php)   │
                      └──────────────┘
                             │
                             │ Route to
                             ▼
[3] CONTROLLER         ┌──────────────────────┐
                       │ RfidScanController   │
                       │ @processRfid()       │
                       └──────────────────────┘
                             │
                             ├─[A] Validasi Input
                             │    (UID, Location)
                             │
                             ├─[B] Query Database
                             │    via Model
                             ▼
[4] MODEL              ┌──────────────────────┐
                       │   Eloquent Models    │
                       │ ┌────────────────┐   │
                       │ │ RfidCard       │   │
                       │ │ User           │   │
                       │ │ Location       │   │
                       │ │ AccessRight    │   │
                       │ │ TrackingLog    │   │
                       │ └────────────────┘   │
                       └──────────────────────┘
                             │
                             │ SQL Query
                             ▼
[5] DATABASE           ┌──────────────────────┐
                       │      MySQL DB        │
                       │ ┌────────────────┐   │
                       │ │ rfid_cards     │   │
                       │ │ users          │   │
                       │ │ locations      │   │
                       │ │ access_rights  │   │
                       │ │ tracking_logs  │   │
                       │ └────────────────┘   │
                       └──────────────────────┘
                             │
                             │ Result
                             ▼
[6] CONTROLLER         ┌──────────────────────┐
    (Return)           │ Process Business     │
                       │ Logic:               │
                       │ • Check Access       │
                       │ • Determine Status   │
                       │ • Log Tracking       │
                       │ • Return Response    │
                       └──────────────────────┘
                             │
                             │ JSON Response
                             ▼
[7] BROWSER            ┌──────────────────────┐
    (Response)         │ JavaScript Updates   │
                       │ DOM Elements:        │
                       │ • Nama Pengguna      │
                       │ • Lokasi             │
                       │ • Waktu              │
                       │ • Status Akses       │
                       └──────────────────────┘
```

### 2.2 Detail Setiap Tahap

**Tahap 1 - RFID Reader:**
- RFID Reader USB beroperasi dalam mode HID (Human Interface Device)
- Device terbaca sebagai keyboard oleh sistem operasi
- Saat kartu RFID di-tap, reader mengirimkan UID sebagai keystrokes
- Tidak memerlukan driver khusus

**Tahap 2 - Browser (Client):**
- Input field dengan autofocus menangkap UID
- JavaScript event listener mendeteksi Enter key
- AJAX/Fetch mengirim data ke server:
  ```javascript
  {
    uid: "ABC12345",
    location: "Dekanat",
    timestamp: "2025-12-14 10:30:00"
  }
  ```

**Tahap 3 - Routing:**
- Laravel Router menerima HTTP request
- Mencocokkan URL dengan route definition
- Mengarahkan ke controller yang sesuai

**Tahap 4 - Controller:**
- Validasi input
- Query ke database melalui Model
- Proses logika bisnis
- Return response

**Tahap 5 - Model:**
- Eloquent ORM menyediakan interface ke database
- Menjalankan query dengan syntax PHP
- Mengembalikan data sebagai object

**Tahap 6 - Database:**
- Menyimpan dan mengambil data
- Enforces constraints (foreign key, unique, dll)
- Transaction management

**Tahap 7 - Response:**
- Controller mengirim JSON response
- JavaScript update UI
- User melihat hasil scan

---

## 3. PERAN BLADE TEMPLATE

### 3.1 Apa itu Blade?

**Blade** adalah template engine bawaan Laravel yang powerful dan mudah digunakan.

**Keunggulan Blade:**
- Syntax yang bersih dan ekspresif
- Template inheritance (extend layout)
- Component reusability
- Directive khusus (@if, @foreach, @csrf, dll)
- Compiled menjadi PHP murni (performa tinggi)

### 3.2 Struktur Blade Template

```
resources/views/
├── layouts/
│   └── app.blade.php          (Master layout)
├── partials/
│   ├── header.blade.php       (Header component)
│   ├── footer.blade.php       (Footer component)
│   └── sidebar.blade.php      (Sidebar component)
├── rfid/
│   ├── scan.blade.php         (Halaman scan RFID)
│   └── result.blade.php       (Hasil scan)
├── dashboard/
│   └── index.blade.php        (Dashboard monitoring)
└── visitors/
    ├── index.blade.php        (Daftar tamu)
    └── create.blade.php       (Form registrasi)
```

### 3.3 Contoh Penggunaan Blade

**Layout Master (app.blade.php):**
```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - VMS Unhan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @yield('styles')
</head>
<body>
    @include('partials.header')
    
    <div class="container">
        @yield('content')
    </div>
    
    @include('partials.footer')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
```

**Child View (scan.blade.php):**
```blade
@extends('layouts.app')

@section('title', 'Scan RFID')

@section('content')
    <h1>Scan Kartu RFID</h1>
    <input type="text" id="rfid-input" autofocus>
@endsection

@section('scripts')
    <script src="{{ asset('js/rfid-scan.js') }}"></script>
@endsection
```

---

## 4. SKEMA KOMUNIKASI HTTP

### 4.1 Request-Response Cycle

```
┌─────────────────────────────────────────────────────────┐
│               REQUEST-RESPONSE CYCLE                     │
└─────────────────────────────────────────────────────────┘

CLIENT (Browser)                SERVER (Laravel)
─────────────────               ────────────────

[1] HTTP REQUEST
POST /rfid/scan ──────────►  [2] Laravel Receives Request
                                    │
Content-Type: application/json      │ Middleware Pipeline:
Body: {                             │ - VerifyCsrfToken
  "uid": "ABC12345",                │ - StartSession
  "location": "Dekanat"             │ - Authentication (optional)
}                                   │
                                    ▼
                              [3] Route Matching
                                  web.php matches:
                                  POST /rfid/scan
                                    │
                                    ▼
                              [4] Controller Execution
                                  RfidScanController
                                  @processRfid()
                                    │
                                    ├─ Validate input
                                    ├─ Query database
                                    ├─ Business logic
                                    └─ Prepare response
                                    │
                                    ▼
                              [5] HTTP RESPONSE
[6] Receive Response   ◄──────  Status: 200 OK
                                Content-Type: application/json
JavaScript parses JSON          Body: {
Updates DOM                       "success": true,
Shows result to user              "data": {
                                    "name": "John Doe",
                                    "location": "Dekanat",
                                    "time": "10:30:00",
                                    "status": "accepted"
                                  }
                                }
```

### 4.2 HTTP Methods yang Digunakan

| Method | Route | Purpose | Example |
|--------|-------|---------|---------|
| GET | `/scan/{location}` | Tampilkan halaman scan | Browser visit |
| POST | `/rfid/scan` | Process scan RFID | AJAX submit |
| GET | `/dashboard` | Tampilkan dashboard | Browser visit |
| GET | `/api/tracking/active` | Get active visitors | API call |
| POST | `/visitors` | Registrasi tamu baru | Form submit |

### 4.3 Status Code

- **200 OK:** Request berhasil
- **201 Created:** Resource baru dibuat
- **400 Bad Request:** Input tidak valid
- **401 Unauthorized:** Tidak ter-autentikasi
- **403 Forbidden:** Tidak punya akses
- **404 Not Found:** Resource tidak ditemukan
- **422 Unprocessable Entity:** Validasi gagal
- **500 Internal Server Error:** Error server

---

## 5. ENTITY RELATIONSHIP DIAGRAM (ERD)

### 5.1 Diagram Relasi Antar Tabel

```
┌─────────────────────────────────────────────────────────────────┐
│                  ENTITY RELATIONSHIP DIAGRAM                     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────┐         ┌─────────────────┐
│     USERS       │         │  RFID_CARDS     │
├─────────────────┤         ├─────────────────┤
│ • id (PK)       │◄───1:N──│ • id (PK)       │
│ • name          │         │ • uid (UNIQUE)  │
│ • email         │         │ • user_id (FK)  │
│ • phone         │         │ • status        │
│ • user_type     │         │ • registered_at │
│ • created_at    │         │ • created_at    │
└─────────────────┘         └─────────────────┘
        │                            │
        │                            │
        │ 1                          │ N
        │                            │
        ▼ N                          ▼ 1
┌─────────────────┐         ┌─────────────────┐
│ ACCESS_RIGHTS   │         │ TRACKING_LOGS   │
├─────────────────┤         ├─────────────────┤
│ • id (PK)       │         │ • id (PK)       │
│ • user_id (FK)  │         │ • rfid_card_id  │
│ • location_id   │         │ • user_id (FK)  │
│ • can_access    │         │ • location_id   │
│ • created_at    │         │ • action_type   │
└─────────────────┘         │ • status        │
        │                   │ • scanned_at    │
        │ N                 │ • created_at    │
        │                   └─────────────────┘
        │                            │
        │                            │ N
        │ 1                          │
        ▼                            ▼ 1
┌─────────────────┐         ┌─────────────────┐
│   LOCATIONS     │◄────────│                 │
├─────────────────┤   N:1   │                 │
│ • id (PK)       │         │                 │
│ • name          │         │                 │
│ • code          │         │                 │
│ • description   │         │                 │
│ • is_active     │         │                 │
│ • created_at    │         │                 │
└─────────────────┘         └─────────────────┘

LEGEND:
PK = Primary Key
FK = Foreign Key
1:N = One to Many relationship
```

### 5.2 Penjelasan Relasi

**1. USERS ↔ RFID_CARDS (One to Many)**
- Satu user dapat memiliki banyak kartu RFID
- Setiap kartu RFID hanya dimiliki oleh satu user
- Relasi: `User hasMany RfidCard`, `RfidCard belongsTo User`

**2. USERS ↔ ACCESS_RIGHTS (One to Many)**
- Satu user dapat memiliki hak akses ke banyak lokasi
- Setiap access right terkait dengan satu user
- Relasi: `User hasMany AccessRight`, `AccessRight belongsTo User`

**3. USERS ↔ TRACKING_LOGS (One to Many)**
- Satu user dapat memiliki banyak log tracking
- Setiap log terkait dengan satu user
- Relasi: `User hasMany TrackingLog`, `TrackingLog belongsTo User`

**4. LOCATIONS ↔ ACCESS_RIGHTS (One to Many)**
- Satu lokasi dapat diakses oleh banyak user
- Setiap access right terkait dengan satu lokasi
- Relasi: `Location hasMany AccessRight`, `AccessRight belongsTo Location`

**5. LOCATIONS ↔ TRACKING_LOGS (One to Many)**
- Satu lokasi dapat memiliki banyak log tracking
- Setiap log terkait dengan satu lokasi
- Relasi: `Location hasMany TrackingLog`, `TrackingLog belongsTo Location`

**6. RFID_CARDS ↔ TRACKING_LOGS (One to Many)**
- Satu kartu RFID dapat memiliki banyak log tracking
- Setiap log terkait dengan satu kartu RFID
- Relasi: `RfidCard hasMany TrackingLog`, `TrackingLog belongsTo RfidCard`

---

## 6. PENJELASAN FUNGSI SETIAP TABEL

### 6.1 Tabel USERS

**Fungsi:**
Menyimpan data pengguna sistem (tamu dan pegawai Unhan).

**Kolom Penting:**
- `user_type`: Membedakan tamu dan pegawai (enum: 'guest', 'employee')
- `name`: Nama lengkap
- `email`: Email untuk identifikasi
- `phone`: Nomor telepon

**Use Case:**
- Registrasi tamu yang berkunjung
- Registrasi pegawai Unhan
- Identifikasi pemilik kartu RFID
- Basis validasi hak akses

### 6.2 Tabel RFID_CARDS

**Fungsi:**
Menyimpan data kartu RFID dan relasinya dengan user.

**Kolom Penting:**
- `uid`: UID unik dari kartu RFID (misal: "E004012345ABCD")
- `user_id`: Relasi ke pemilik kartu
- `status`: Status kartu (enum: 'active', 'inactive', 'blocked')
- `registered_at`: Tanggal registrasi kartu

**Use Case:**
- Menyimpan UID yang dibaca dari RFID reader
- Mapping UID ke user tertentu
- Menonaktifkan kartu yang hilang
- Tracking kartu yang digunakan

### 6.3 Tabel LOCATIONS

**Fungsi:**
Menyimpan data lokasi virtual tempat RFID reader "berada".

**Kolom Penting:**
- `name`: Nama lokasi (misal: "Dekanat", "Aula", "Lab Informatika")
- `code`: Kode unik lokasi (misal: "DEK", "AULA", "LAB-IT")
- `description`: Deskripsi lokasi
- `is_active`: Status aktif/nonaktif lokasi

**Use Case:**
- Menentukan lokasi simulasi RFID reader
- Basis pengecekan hak akses
- Tracking perpindahan user antar lokasi
- Konfigurasi lokasi baru

### 6.4 Tabel ACCESS_RIGHTS

**Fungsi:**
Menyimpan hak akses user ke lokasi tertentu.

**Kolom Penting:**
- `user_id`: User yang memiliki hak akses
- `location_id`: Lokasi yang dapat diakses
- `can_access`: Status akses (boolean)

**Use Case:**
- Validasi apakah user boleh masuk lokasi
- Menentukan status "Diterima" atau "Ditolak"
- Konfigurasi akses untuk pegawai/tamu tertentu
- Revoke akses jika diperlukan

**Contoh Data:**
```
user_id | location_id | can_access
--------|-------------|------------
1       | 1 (Dekanat) | true       → User 1 boleh masuk Dekanat
1       | 2 (Aula)    | true       → User 1 boleh masuk Aula
2       | 1 (Dekanat) | false      → User 2 tidak boleh masuk Dekanat
```

### 6.5 Tabel TRACKING_LOGS

**Fungsi:**
Mencatat semua aktivitas scan RFID (masuk, keluar, perpindahan).

**Kolom Penting:**
- `rfid_card_id`: Kartu RFID yang di-scan
- `user_id`: User yang melakukan scan
- `location_id`: Lokasi saat scan
- `action_type`: Jenis aksi (enum: 'entry', 'exit', 'move', 'denied')
- `status`: Status scan (enum: 'accepted', 'denied')
- `scanned_at`: Waktu scan

**Use Case:**
- Mencatat jam masuk dan keluar
- Tracking lokasi user real-time
- Histori perpindahan user
- Audit trail untuk keamanan
- Laporan aktivitas

**Logika Action Type:**
1. **entry**: Scan pertama di lokasi (belum ada log aktif)
2. **exit**: Scan kedua di lokasi yang sama (keluar dari lokasi)
3. **move**: Scan di lokasi berbeda (pindah lokasi tanpa exit)
4. **denied**: Scan ditolak (tidak punya akses)

**Contoh Skenario:**
```
Time  | Location | Action | Status   | Keterangan
------|----------|--------|----------|-------------
10:00 | Dekanat  | entry  | accepted | Masuk Dekanat
11:30 | Aula     | move   | accepted | Pindah ke Aula (tidak exit Dekanat dulu)
12:00 | Aula     | exit   | accepted | Keluar dari Aula
12:15 | Lab IT   | entry  | denied   | Ditolak (tidak punya akses ke Lab IT)
```

---

## 7. KEUNIKAN SISTEM (SIMULASI 1 RFID READER)

### 7.1 Konsep Virtual Location

**Masalah:**
- Hanya tersedia 1 unit RFID reader fisik
- Dalam sistem nyata, setiap pintu butuh 1 reader
- Bagaimana mensimulasikan multi-lokasi?

**Solusi:**
- Lokasi tidak ditentukan oleh hardware
- Lokasi ditentukan secara virtual oleh software
- RFID reader "berpindah" secara virtual

### 7.2 Implementasi Dua Mode Simulasi

**Mode A - URL-Based Location:**
```
http://localhost/scan/dekanat
http://localhost/scan/aula
http://localhost/scan/lab-informatika

Route: /scan/{location}
Location determined by: URL parameter
```

**Mode B - Dropdown-Based Location:**
```
<select id="location">
    <option value="dekanat">Dekanat</option>
    <option value="aula">Aula</option>
    <option value="lab-informatika">Lab Informatika</option>
</select>

Location determined by: User selection
```

### 7.3 Keuntungan Pendekatan Ini

✅ **Fleksibel:** Mudah menambah lokasi baru tanpa hardware
✅ **Ekonomis:** Hanya butuh 1 RFID reader untuk testing
✅ **Demonstrasi:** Cocok untuk presentasi skripsi
✅ **Skalabel:** Mudah diubah ke multi-reader saat implementasi nyata

---

## 8. FLOWCHART SISTEM

### 8.1 Flowchart Proses Scan RFID

```
               START
                 │
                 ▼
        ┌────────────────┐
        │  User tap RFID │
        │  card to reader│
        └────────────────┘
                 │
                 ▼
        ┌────────────────┐
        │ Reader sends   │
        │ UID to browser │
        └────────────────┘
                 │
                 ▼
        ┌────────────────┐
        │ AJAX POST to   │
        │ /rfid/scan     │
        └────────────────┘
                 │
                 ▼
        ┌────────────────┐
        │ Validate UID & │
        │ Location       │
        └────────────────┘
                 │
                 ▼
         ┌──────────────┐
         │  Find RFID   │
         │  Card in DB  │
         └──────────────┘
                 │
         ┌───────┴────────┐
         │                │
         ▼ Found          ▼ Not Found
    ┌────────┐      ┌──────────┐
    │Get User│      │  Return  │
    │  Data  │      │  Error   │
    └────────┘      └──────────┘
         │                │
         ▼                ▼
    ┌────────┐         END
    │ Check  │
    │ Access │
    │ Rights │
    └────────┘
         │
   ┌─────┴──────┐
   │            │
   ▼ Allowed    ▼ Denied
┌────────┐  ┌────────┐
│Process │  │  Log   │
│Entry/  │  │Denied  │
│Exit/   │  │Access  │
│Move    │  └────────┘
└────────┘      │
   │            │
   ▼            ▼
┌────────┐  ┌────────┐
│  Save  │  │Return  │
│Tracking│  │Response│
│  Log   │  │"Denied"│
└────────┘  └────────┘
   │            │
   ▼            │
┌────────┐      │
│Return  │      │
│Response│      │
│"Success"│     │
└────────┘      │
   │            │
   └─────┬──────┘
         │
         ▼
    ┌────────┐
    │Update  │
    │Browser │
    │  UI    │
    └────────┘
         │
         ▼
       END
```

---

## 9. KEAMANAN SISTEM

### 9.1 Aspek Keamanan yang Diimplementasikan

**1. CSRF Protection:**
```php
// Semua form dan AJAX POST request harus include CSRF token
<meta name="csrf-token" content="{{ csrf_token() }}">
```

**2. Input Validation:**
```php
// Validasi UID format dan lokasi
$validated = $request->validate([
    'uid' => 'required|string|max:255',
    'location' => 'required|exists:locations,code'
]);
```

**3. SQL Injection Prevention:**
```php
// Eloquent ORM otomatis escape query
// Tidak pernah raw SQL tanpa parameter binding
```

**4. Access Control:**
```php
// Pengecekan hak akses sebelum izinkan masuk
$hasAccess = AccessRight::where('user_id', $user->id)
    ->where('location_id', $location->id)
    ->where('can_access', true)
    ->exists();
```

**5. Audit Trail:**
```php
// Semua aksi tercatat di tracking_logs
// Tidak bisa dihapus, hanya bisa ditambah
```

---

## 10. SKALABILITAS

### 10.1 Dari Simulasi ke Implementasi Nyata

**Perubahan yang Diperlukan:**

**1. Multi RFID Reader:**
```php
// Tambah kolom rfid_reader_id di tracking_logs
Schema::table('tracking_logs', function (Blueprint $table) {
    $table->foreignId('rfid_reader_id')->constrained();
});

// Setiap reader punya lokasi tetap
// Tidak perlu pilih lokasi manual
```

**2. Real-time Updates:**
```php
// Gunakan Laravel Broadcasting + WebSocket
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('tracking', function () {
    return true;
});

// Kirim event saat ada scan baru
event(new RfidScanned($trackingLog));
```

**3. Optimasi Database:**
```php
// Index untuk query cepat
Schema::table('tracking_logs', function (Blueprint $table) {
    $table->index(['user_id', 'scanned_at']);
    $table->index(['location_id', 'scanned_at']);
});
```

**4. Load Balancing:**
- Multiple server untuk handle concurrent scan
- Redis untuk caching
- Queue untuk proses asynchronous

---

## KESIMPULAN

Arsitektur sistem VMS ini dirancang dengan prinsip:
- **Modular:** Mudah dikembangkan dan dimaintain
- **Scalable:** Dapat ditingkatkan ke sistem multi-reader
- **Secure:** Implementasi best practice keamanan
- **Academic-Valid:** Sesuai untuk penelitian skripsi
- **Practical:** Dapat diimplementasikan dengan resource terbatas

Sistem ini membuktikan bahwa dengan arsitektur yang tepat, 1 RFID reader cukup untuk mensimulasikan sistem multi-lokasi, sambil tetap mempertahankan integritas data dan validitas akademik.
