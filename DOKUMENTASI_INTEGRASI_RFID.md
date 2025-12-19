# DOKUMENTASI TEKNIS & INTEGRASI RFID
## Visitor Management System - Universitas Pertahanan RI

---

## 1. INTEGRASI RFID USB (13.56 MHz)

### 1.1 Cara Kerja RFID USB sebagai Keyboard (HID Mode)

**Konsep Dasar:**

RFID Reader USB beroperasi dalam mode **HID (Human Interface Device)**, yang membuatnya terdeteksi oleh sistem operasi sebagai **keyboard biasa**.

```
┌─────────────────────────────────────────────────────────┐
│              ALUR KERJA RFID USB HID                     │
└─────────────────────────────────────────────────────────┘

[1] KARTU RFID               [2] RFID READER
    ┌─────────┐                  ┌────────┐
    │  UID:   │ ──RF Signal──►  │ Reader │
    │E0041234 │    (13.56MHz)    │  USB   │
    └─────────┘                  └────────┘
                                      │
                                      │ USB HID Protocol
                                      │ (Keyboard Mode)
                                      ▼
[3] SISTEM OPERASI          [4] BROWSER
    ┌───────────────┐          ┌──────────────┐
    │  Terdeteksi   │────►    │ Input Field  │
    │  sebagai      │          │ menerima     │
    │  Keyboard     │          │ keystroke    │
    └───────────────┘          └──────────────┘
                                      │
                                      ▼
                              ┌──────────────┐
                              │ UID String:  │
                              │ "E0041234"   │
                              │ + Enter Key  │
                              └──────────────┘
```

**Langkah-langkah Detail:**

1. **Kartu RFID di-tap ke reader**
   - Kartu mengeluarkan sinyal RF (Radio Frequency) 13.56 MHz
   - Reader membaca UID (Unique Identifier) dari kartu
   - Contoh UID: `E004012345ABCD`

2. **RFID Reader mengkonversi UID ke keystroke**
   - Reader mengubah UID menjadi karakter ASCII
   - Mengirimkan karakter satu per satu via USB HID protocol
   - Menambahkan karakter `Enter` di akhir (konfigurasi reader)

3. **Sistem Operasi menerima input**
   - Windows/Linux mendeteksi reader sebagai keyboard
   - Input diterima di aplikasi yang aktif (focused)
   - Tidak perlu driver tambahan (plug and play)

4. **Browser/Aplikasi menerima data**
   - Input field yang di-focus menerima UID sebagai text
   - JavaScript menangkap event `keypress` atau `input`
   - Saat `Enter` terdeteksi, trigger function scan

---

### 1.2 Alasan Tidak Membutuhkan Driver Tambahan

**1. HID (Human Interface Device) Standard:**
   - USB HID adalah protokol standar yang didukung semua OS modern
   - Windows, Linux, macOS sudah built-in support HID
   - Device langsung dikenali saat di-plug tanpa instalasi

**2. Klasifikasi sebagai Keyboard:**
   - RFID reader dikonfigurasi sebagai "keyboard wedge"
   - OS menganggap reader = keyboard biasa
   - Tidak ada perbedaan dengan keyboard fisik dari sisi OS

**3. Konfigurasi Pabrik:**
   - Sebagian besar RFID reader USB sudah dikonfigurasi HID mode
   - Firmware reader menangani konversi RF → USB
   - Plug and play out of the box

**Bukti Teknis:**
```bash
# Linux: Cek device yang terdeteksi
lsusb
# Output: Bus 001 Device 005: ID xxxx:xxxx USB Keyboard

# Windows: Device Manager
# Akan muncul di "Keyboards" bukan "Unknown Device"
```

---

### 1.3 Cara Browser Membaca UID RFID

**HTML:**
```html
<input type="text" 
       id="rfid-input" 
       class="form-control" 
       placeholder="Tap kartu RFID..." 
       autofocus>
```

**JavaScript - Method 1: Event Listener**
```javascript
// Mendeteksi input RFID dengan event keypress
document.getElementById('rfid-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        e.preventDefault(); // Prevent form submit
        
        const uid = this.value.trim();
        console.log('UID RFID:', uid);
        
        // Process scan
        processRfidScan(uid);
        
        // Clear input
        this.value = '';
    }
});
```

**JavaScript - Method 2: Input Event**
```javascript
// Auto-submit saat Enter terdeteksi
$('#rfid-input').on('keypress', function(e) {
    if (e.which === 13) { // Enter key
        e.preventDefault();
        processScan();
    }
});

function processScan() {
    const uid = $('#rfid-input').val().trim();
    const location = $('#location-select').val();
    
    // AJAX ke server
    $.ajax({
        url: '/rfid/scan',
        method: 'POST',
        data: {
            uid: uid,
            location: location,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            showResult(response);
        },
        error: function(xhr) {
            showError(xhr.responseJSON.message);
        }
    });
}
```

**Penjelasan Teknis:**

1. **autofocus**: Input field selalu aktif saat halaman dibuka
2. **keypress event**: Menangkap setiap keystroke dari reader
3. **Enter detection**: UID lengkap sudah dikirim, siap diproses
4. **AJAX POST**: Kirim UID ke server tanpa reload halaman
5. **Response handling**: Tampilkan hasil scan secara real-time

---

### 1.4 Cara Simulasi Tanpa Alat RFID Fisik

**Untuk Testing & Development:**

**Method 1: Manual Input**
```javascript
// User bisa ketik UID manual dan tekan Enter
// Contoh ketik: E004012345ABCD
// Tekan Enter
// Sistem akan proses seperti scan asli
```

**Method 2: Button Trigger**
```html
<button onclick="simulateScan('E004012345ABCD')">
    Simulasi Scan Kartu A
</button>

<script>
function simulateScan(uid) {
    $('#rfid-input').val(uid);
    $('#rfid-input').trigger($.Event('keypress', { which: 13 }));
}
</script>
```

**Method 3: Generate Random UID**
```javascript
function generateRandomUID() {
    const chars = '0123456789ABCDEF';
    let uid = 'E00401';
    for (let i = 0; i < 8; i++) {
        uid += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return uid;
}

// Simulate scan with random UID
$('#simulate-btn').click(function() {
    const uid = generateRandomUID();
    $('#rfid-input').val(uid);
    processScan();
});
```

**Method 4: Seeder Database**
```php
// database/seeders/RfidCardSeeder.php
public function run()
{
    $testCards = [
        ['uid' => 'E004012345ABCD', 'user_id' => 1],
        ['uid' => 'E004012345ABCE', 'user_id' => 2],
        ['uid' => 'E004012345ABCF', 'user_id' => 3],
    ];

    foreach ($testCards as $card) {
        RfidCard::create($card);
    }
}
```

**Cara Testing:**
1. Seed database dengan test cards
2. Buka halaman `/scan`
3. Ketik UID test: `E004012345ABCD`
4. Tekan Enter
5. Sistem akan proses seperti scan asli

---

## 2. SIMULASI MULTI-LOKASI DENGAN 1 RFID READER

### 2.1 Konsep Simulasi

**Masalah Nyata:**
- Sistem real butuh 1 RFID reader per pintu/lokasi
- Contoh: 5 lokasi = 5 RFID reader
- Budget terbatas, hanya ada 1 reader

**Solusi Simulasi:**
- Lokasi ditentukan secara **virtual** oleh software
- 1 reader fisik bisa "berpindah" lokasi secara virtual
- Lokasi aktif ditentukan oleh user, bukan hardware

### 2.2 Implementasi Mode A - URL-Based Location

**Konsep:**
Setiap halaman scan mewakili 1 lokasi fisik.

**Route:**
```php
// routes/web.php
Route::get('/scan/{location}', [RfidScanController::class, 'showScanPage']);

// Contoh URL:
// http://localhost/scan/dekanat       → Reader di Dekanat
// http://localhost/scan/aula          → Reader di Aula
// http://localhost/scan/lab-informatika → Reader di Lab Informatika
```

**Controller:**
```php
public function showScanPage(?string $locationCode = null)
{
    $selectedLocation = null;
    if ($locationCode) {
        $selectedLocation = Location::byCode($locationCode)->first();
    }
    
    return view('rfid.scan', compact('selectedLocation'));
}
```

**View:**
```blade
@if($selectedLocation)
    <h2>Lokasi: {{ $selectedLocation->name }}</h2>
    <input type="hidden" id="location-code" value="{{ $selectedLocation->code }}">
@endif
```

**Keuntungan:**
- 1 URL = 1 lokasi tetap
- Cocok untuk deployment multi-device
- Setiap device bisa dibuka di URL berbeda
- Contoh: Tablet A di `/scan/dekanat`, Tablet B di `/scan/aula`

### 2.3 Implementasi Mode B - Dropdown-Based Location

**Konsep:**
User memilih lokasi aktif reader via dropdown.

**View:**
```blade
<select id="location-select" class="form-select">
    <option value="">-- Pilih Lokasi --</option>
    @foreach($locations as $location)
        <option value="{{ $location->code }}">
            {{ $location->name }}
        </option>
    @endforeach
</select>
```

**JavaScript:**
```javascript
function processScan() {
    const uid = $('#rfid-input').val();
    const location = $('#location-select').val();
    
    if (!location) {
        alert('Pilih lokasi terlebih dahulu!');
        return;
    }
    
    // AJAX dengan location yang dipilih
    $.post('/rfid/scan', { uid, location });
}
```

**Keuntungan:**
- Fleksibel: bisa ganti lokasi tanpa ganti URL
- Cocok untuk 1 device yang berpindah-pindah
- Simulasi petugas membawa reader ke berbagai lokasi

### 2.4 Perbandingan Mode A vs Mode B

| Aspek | Mode A (URL) | Mode B (Dropdown) |
|-------|--------------|-------------------|
| **Deployment** | Multi-device (1 URL per device) | Single-device (ganti lokasi manual) |
| **Skenario** | Reader tetap di lokasi | Reader dibawa berpindah |
| **Realisme** | Lebih realistis (fixed location) | Simulasi portable reader |
| **Kemudahan** | Sekali setting, tidak perlu ganti | Harus pilih lokasi setiap kali |
| **Cocok untuk** | Implementasi nyata multi-reader | Demo & testing |

### 2.5 Visualisasi Skenario

**Skenario Real (Multi-Reader):**
```
Gedung A
├── Pintu Utama    → RFID Reader #1 (fixed)
├── Ruang Dekanat  → RFID Reader #2 (fixed)
└── Lab Komputer   → RFID Reader #3 (fixed)

Setiap reader punya lokasi tetap di database
```

**Skenario Simulasi (1 Reader):**
```
RFID Reader #1 (Virtual Location)
├── Pilih "Pintu Utama"     → scan → log ke lokasi 1
├── Pilih "Ruang Dekanat"   → scan → log ke lokasi 2
└── Pilih "Lab Komputer"    → scan → log ke lokasi 3

Reader sama, lokasi ditentukan software
```

---

## 3. SPESIFIKASI TEKNIS RFID

### 3.1 RFID Reader USB 13.56 MHz

**Spesifikasi Umum:**
- **Frekuensi**: 13.56 MHz (High Frequency)
- **Protokol**: ISO 14443A/B, ISO 15693
- **Interface**: USB 2.0 (HID Mode)
- **Jarak Baca**: 0-10 cm
- **Kompatibilitas**: MIFARE Classic, NTAG, iCODE, dll
- **Output**: ASCII text + Enter (configurable)
- **Power**: Bus-powered via USB (no external power)

**Format UID Output:**
```
Default: E004012345ABCD<CR>
Hexadecimal: 14 digit (7 byte UID)
Suffix: Carriage Return (Enter key)
```

**Keuntungan 13.56 MHz:**
- ✅ Jarak baca stabil
- ✅ Tidak mudah interferensi
- ✅ Banyak kartu kompatibel (MIFARE, NTAG, dll)
- ✅ Standar internasional (ISO 14443)
- ✅ Harga terjangkau

**Kartu RFID yang Kompatibel:**
1. **MIFARE Classic 1K/4K**: Kartu paling umum
2. **MIFARE Ultralight**: Lebih murah, read-only
3. **NTAG213/215/216**: NFC-enabled, bisa tulis ulang
4. **ISO 15693**: Kartu jarak jauh

### 3.2 Contoh Produk RFID Reader USB

**Brand Populer:**
1. **ACR122U** (Advanced Card Systems)
   - Harga: ~500-700 ribu
   - Fitur lengkap, support NFC
   
2. **RDM6300** (Generic Chinese)
   - Harga: ~150-300 ribu
   - Basic, cocok untuk project

3. **ZKTeco** (Security brand)
   - Harga: ~400-600 ribu
   - Industrial quality

---

## 4. TROUBLESHOOTING & TIPS

### 4.1 Reader Tidak Terdeteksi

**Solusi:**
1. Cek USB port (coba port lain)
2. Cek Device Manager (Windows) atau `lsusb` (Linux)
3. Pastikan reader dalam HID mode (bukan COM port mode)
4. Restart computer

### 4.2 UID Tidak Muncul di Input

**Solusi:**
1. Pastikan input field di-focus (`autofocus` attribute)
2. Cek apakah cursor berada di input field
3. Test dengan keyboard fisik (ketik manual)
4. Reader mungkin perlu konfigurasi output format

### 4.3 Kartu Tidak Terbaca

**Solusi:**
1. Pastikan kartu kompatibel (13.56 MHz)
2. Jarak kartu terlalu jauh (< 10 cm)
3. Kartu rusak atau tidak terformat
4. Reader butuh di-reset (cabut-pasang USB)

### 4.4 Double Scan (UID Terkirim 2x)

**Solusi:**
```javascript
let isProcessing = false;

function processScan() {
    if (isProcessing) return; // Prevent double scan
    
    isProcessing = true;
    
    $.ajax({
        url: '/rfid/scan',
        // ... ajax config
        complete: function() {
            isProcessing = false; // Allow next scan
        }
    });
}
```

---

## 5. SECURITY BEST PRACTICES

### 5.1 Validasi UID

```php
// JANGAN PERCAYA INPUT MENTAH
public function processRfid(Request $request)
{
    // Validasi format UID
    $validated = $request->validate([
        'uid' => 'required|string|max:255|regex:/^[A-F0-9]+$/',
        'location' => 'required|exists:locations,code',
    ]);
    
    // Sanitasi
    $uid = strtoupper(trim($validated['uid']));
    
    // Cek di database
    $card = RfidCard::byUid($uid)->first();
    
    if (!$card) {
        // Log attempted unauthorized scan
        Log::warning('Unknown UID scanned', ['uid' => $uid]);
        return response()->json(['message' => 'Kartu tidak terdaftar'], 404);
    }
    
    // Continue processing...
}
```

### 5.2 Rate Limiting

```php
// Prevent spam scanning
public function processRfid(Request $request)
{
    // Max 10 scans per minute per IP
    $key = 'rfid_scan_' . $request->ip();
    
    if (Cache::has($key) && Cache::get($key) >= 10) {
        return response()->json(['message' => 'Too many scans'], 429);
    }
    
    Cache::increment($key, 1);
    Cache::put($key, Cache::get($key), 60); // 1 minute TTL
    
    // Process scan...
}
```

### 5.3 Audit Trail

```php
// Semua aktivitas tercatat
TrackingLog::create([
    'rfid_card_id' => $card->id,
    'user_id' => $user->id,
    'location_id' => $location->id,
    'action_type' => $actionType,
    'status' => $status,
    'scanned_at' => now(),
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

---

## KESIMPULAN INTEGRASI RFID

✅ **RFID USB HID Mode** = Plug and Play, tidak perlu driver  
✅ **Browser** dapat langsung baca UID sebagai keyboard input  
✅ **Simulasi 1 reader** untuk multi-lokasi sangat memungkinkan  
✅ **Validasi akademik** terpenuhi untuk skripsi  
✅ **Scalable** untuk implementasi multi-reader di masa depan  

Sistem ini membuktikan bahwa RFID integration pada web application dapat dilakukan dengan mudah dan efisien, tanpa memerlukan hardware khusus atau driver complicated.
