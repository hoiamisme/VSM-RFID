# VISITOR MANAGEMENT SYSTEM (VMS)
## Universitas Pertahanan Republik Indonesia

[![Laravel](https://img.shields.io/badge/Laravel-10.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple.svg)](https://getbootstrap.com)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![RFID](https://img.shields.io/badge/RFID-13.56MHz-green.svg)](#)

---

## 📖 Tentang Proyek

**Visitor Management System (VMS)** adalah sistem manajemen pengunjung berbasis web dengan integrasi teknologi RFID untuk Pos Jaga Ksatrian Universitas Pertahanan Republik Indonesia.

### Fitur Utama:
✅ **Registrasi Pengunjung** - Daftar tamu dan pegawai dengan data lengkap  
✅ **RFID Integration** - Scan kartu RFID USB 13.56 MHz (HID Mode)  
✅ **Multi-Location Tracking** - Simulasi tracking di berbagai lokasi  
✅ **Access Control** - Validasi hak akses per lokasi  
✅ **Dashboard Real-time** - Monitoring pengunjung aktif  
✅ **Audit Trail** - Pencatatan lengkap semua aktivitas  
✅ **Reports** - Laporan aktivitas dan statistik  

### Teknologi yang Digunakan:
- **Backend**: Laravel 10.x (PHP 8.1+)
- **Frontend**: Bootstrap 5.3, jQuery, JavaScript
- **Database**: MySQL 5.7+
- **RFID**: USB Reader 13.56 MHz (HID Mode)
- **Architecture**: MVC Pattern

---

## 🎯 Tujuan Penelitian

Skripsi ini bertujuan untuk:
1. Merancang dan membangun sistem VMS berbasis web
2. Mengintegrasikan teknologi RFID untuk otomasi scan
3. Mensimulasikan sistem multi-lokasi dengan 1 RFID reader
4. Menyediakan dashboard monitoring real-time
5. Menghasilkan audit trail untuk keperluan keamanan

---

## 📁 Struktur Project

```
VMS/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php
│   │   ├── RfidScanController.php
│   │   └── VisitorController.php
│   └── Models/
│       ├── User.php
│       ├── RfidCard.php
│       ├── Location.php
│       ├── AccessRight.php
│       └── TrackingLog.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── dashboard/
│       ├── rfid/
│       └── visitors/
├── routes/
│   ├── web.php
│   └── api.php
├── public/
├── DOKUMENTASI_ARSITEKTUR.md
├── DOKUMENTASI_INTEGRASI_RFID.md
├── VALIDASI_AKADEMIK.md
└── PANDUAN_INSTALASI.md
```

---

## 🚀 Quick Start

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js >= 16.x
- RFID Reader USB 13.56 MHz (opsional)

### Instalasi

```bash
# Clone repository
git clone https://github.com/username/vms-unhan.git
cd vms-unhan

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Start development server
php artisan serve
```

Buka browser: http://localhost:8000

---

## 📊 Database Schema

### Entity Relationship Diagram

```
┌─────────────┐         ┌─────────────┐
│    USERS    │◄───1:N──│ RFID_CARDS  │
└─────────────┘         └─────────────┘
      │                        │
      │ 1:N                    │ 1:N
      ▼                        ▼
┌─────────────┐         ┌──────────────┐
│ACCESS_RIGHTS│         │ TRACKING_LOGS│
└─────────────┘         └──────────────┘
      │ N:1                    │ N:1
      │                        │
      ▼                        ▼
┌─────────────┐         ┌──────────────┐
│  LOCATIONS  │◄────────┘              │
└─────────────┘                        │
```

### Tabel Utama:
1. **users** - Data pengguna (tamu & pegawai)
2. **rfid_cards** - Data kartu RFID dan relasinya
3. **locations** - Lokasi virtual untuk tracking
4. **access_rights** - Hak akses user ke lokasi
5. **tracking_logs** - Log semua aktivitas scan

Detail lengkap: Lihat [DOKUMENTASI_ARSITEKTUR.md](DOKUMENTASI_ARSITEKTUR.md)

---

## 🔐 Security Features

- ✅ CSRF Protection
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ Input Validation & Sanitization
- ✅ Access Control per Location
- ✅ Complete Audit Trail
- ✅ Password Hashing (untuk user login - future)

---

## 📱 Simulasi Multi-Lokasi

### Mode A: URL-Based Location
```
http://localhost/scan/dekanat        → Reader di Dekanat
http://localhost/scan/aula           → Reader di Aula
http://localhost/scan/lab-informatika → Reader di Lab IT
```

### Mode B: Dropdown-Based Location
User memilih lokasi aktif reader melalui dropdown di halaman scan.

**Penjelasan lengkap:** [DOKUMENTASI_INTEGRASI_RFID.md](DOKUMENTASI_INTEGRASI_RFID.md)

---

## 🧪 Testing

### Test Manual
```bash
# 1. Seed database
php artisan db:seed

# 2. Buka halaman scan
http://localhost:8000/scan

# 3. Ketik UID test
E004012345ABCD

# 4. Tekan Enter
```

### Test via API
```bash
curl -X POST http://localhost:8000/rfid/scan \
  -H "Content-Type: application/json" \
  -d '{"uid":"E004012345ABCD","location":"DEK"}'
```

**Panduan lengkap:** [PANDUAN_INSTALASI.md](PANDUAN_INSTALASI.md#6-testing-rfid)

---

## 📚 Dokumentasi Lengkap

1. **[DOKUMENTASI_ARSITEKTUR.md](DOKUMENTASI_ARSITEKTUR.md)**
   - Arsitektur MVC Laravel
   - Alur data RFID → Server → Database
   - Entity Relationship Diagram
   - Penjelasan fungsi setiap tabel

2. **[DOKUMENTASI_INTEGRASI_RFID.md](DOKUMENTASI_INTEGRASI_RFID.md)**
   - Cara kerja RFID USB HID Mode
   - Browser membaca UID RFID
   - Simulasi tanpa alat fisik
   - Spesifikasi teknis RFID 13.56 MHz

3. **[VALIDASI_AKADEMIK.md](VALIDASI_AKADEMIK.md)**
   - Justifikasi simulasi 1 RFID reader
   - Batasan penelitian
   - Perbedaan simulasi vs implementasi nyata
   - Kontribusi akademik

4. **[PANDUAN_INSTALASI.md](PANDUAN_INSTALASI.md)**
   - Step-by-step instalasi
   - Setup database dan seeding
   - Testing RFID
   - Troubleshooting

---

## 🎓 Validitas Akademik

**Pertanyaan:** Apakah simulasi dengan 1 RFID reader valid untuk skripsi?

**Jawaban:** **YA**, karena:
- ✅ Fokus penelitian pada software logic, bukan hardware deployment
- ✅ Core functionality dapat divalidasi dengan simulasi
- ✅ Arsitektur scalable untuk upgrade multi-reader
- ✅ Metodologi penelitian robust
- ✅ Dokumentasi lengkap dan jelas

**Baca detail:** [VALIDASI_AKADEMIK.md](VALIDASI_AKADEMIK.md)

---

## 🔄 Upgrade Path

### Current (Simulasi)
- 1 RFID Reader USB
- Manual location selection
- Standalone device

### Future (Production)
- Multiple RFID Readers
- Auto-detect location per device
- Centralized server
- Real-time WebSocket updates
- Mobile app integration

---

## 🤝 Kontribusi

Proyek ini adalah bagian dari skripsi untuk:
- **Mahasiswa**: [Nama Mahasiswa]
- **NIM**: [NIM]
- **Program Studi**: Teknik Informatika
- **Universitas**: Universitas Pertahanan Republik Indonesia
- **Pembimbing**: [Nama Dosen Pembimbing]
- **Tahun**: 2024/2025

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan akademik (skripsi) Universitas Pertahanan RI.

---

## 📞 Kontak

- **Developer**: [Nama Mahasiswa]
- **Email**: [email@example.com]
- **GitHub**: [github.com/username]

---

## 🙏 Acknowledgments

- Universitas Pertahanan Republik Indonesia
- Dosen Pembimbing
- Petugas Pos Jaga Ksatrian Unhan
- Laravel Community
- Bootstrap Team
- Open Source Community

---

## 📈 Statistics

- **Lines of Code**: ~5000+
- **Files**: 50+
- **Database Tables**: 5
- **API Endpoints**: 15+
- **Views**: 10+
- **Documentation Pages**: 4

---

## 🏆 Features Roadmap

**Version 1.0 (Current - Skripsi):**
- ✅ Basic VMS functionality
- ✅ RFID integration (simulated)
- ✅ Dashboard monitoring
- ✅ Reports

**Version 2.0 (Future):**
- ⏳ Multi-reader deployment
- ⏳ Real-time WebSocket
- ⏳ Mobile application
- ⏳ Advanced analytics
- ⏳ Face recognition integration
- ⏳ Email/SMS notifications

---

**Dikembangkan dengan ❤️ untuk Universitas Pertahanan RI**

---

## 🖼️ Screenshots

### Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Scan RFID
![Scan RFID](docs/screenshots/scan.png)

### Visitors List
![Visitors](docs/screenshots/visitors.png)

*(Screenshots akan ditambahkan setelah deployment)*

---

**Last Updated**: December 14, 2025  
**Version**: 1.0.0 (Skripsi Release)
