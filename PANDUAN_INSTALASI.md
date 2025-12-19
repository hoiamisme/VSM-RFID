# PANDUAN INSTALASI & PENGUJIAN
## Visitor Management System (VMS) - Universitas Pertahanan RI

---

## 📋 DAFTAR ISI

1. [Requirements](#1-requirements)
2. [Instalasi Laravel](#2-instalasi-laravel)
3. [Konfigurasi Database](#3-konfigurasi-database)
4. [Setup Project VMS](#4-setup-project-vms)
5. [Seeding Data](#5-seeding-data-testing)
6. [Testing RFID](#6-testing-rfid)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. REQUIREMENTS

### 1.1 Software Requirements

**Wajib Terinstall:**
- ✅ **PHP** >= 8.1
- ✅ **Composer** (PHP Package Manager)
- ✅ **MySQL** >= 5.7 atau **MariaDB** >= 10.3
- ✅ **Node.js** >= 16.x (untuk npm)
- ✅ **Git** (version control)

**Recommended:**
- ✅ **XAMPP** / **Laragon** / **WAMP** (all-in-one package)
- ✅ **Visual Studio Code** (code editor)
- ✅ **Chrome** / **Firefox** (browser)
- ✅ **Postman** (API testing)

### 1.2 Hardware Requirements

**Minimal:**
- CPU: Intel Core i3 / AMD Ryzen 3
- RAM: 4 GB
- Storage: 10 GB free space
- RFID Reader: USB 13.56 MHz (opsional untuk testing)

**Recommended:**
- CPU: Intel Core i5 / AMD Ryzen 5
- RAM: 8 GB
- Storage: 20 GB SSD
- RFID Reader: ACR122U atau equivalent

### 1.3 Cek Versi Software

**Windows:**
```powershell
# Cek PHP version
php -v
# Output: PHP 8.1.x

# Cek Composer
composer --version
# Output: Composer version 2.x

# Cek MySQL
mysql --version
# Output: mysql Ver 8.0.x

# Cek Node.js
node --version
# Output: v18.x.x
```

**Linux/Mac:**
```bash
php -v
composer --version
mysql --version
node --version
```

---

## 2. INSTALASI LARAVEL

### 2.1 Install Composer (jika belum ada)

**Windows:**
1. Download dari: https://getcomposer.org/download/
2. Jalankan `Composer-Setup.exe`
3. Follow instalasi wizard
4. Restart command prompt

**Linux:**
```bash
# Install Composer via script
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

### 2.2 Install Laravel via Composer

```bash
# Install Laravel installer globally
composer global require laravel/installer

# Verifikasi
laravel --version
```

### 2.3 Create New Laravel Project

```bash
# Method 1: Via Laravel installer
laravel new VMS

# Method 2: Via Composer
composer create-project laravel/laravel VMS

# Masuk ke directory project
cd VMS
```

---

## 3. KONFIGURASI DATABASE

### 3.1 Buat Database MySQL

**Via phpMyAdmin:**
1. Buka http://localhost/phpmyadmin
2. Klik "New" / "Baru"
3. Database name: `vms_unhan`
4. Collation: `utf8mb4_unicode_ci`
5. Klik "Create"

**Via MySQL Command Line:**
```sql
-- Login ke MySQL
mysql -u root -p

-- Buat database
CREATE DATABASE vms_unhan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Buat user baru (opsional)
CREATE USER 'vms_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON vms_unhan.* TO 'vms_user'@'localhost';
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

### 3.2 Konfigurasi .env File

```bash
# Copy file .env.example
cp .env.example .env

# Atau di Windows
copy .env.example .env
```

Edit file `.env`:
```env
APP_NAME="VMS Unhan"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vms_unhan
DB_USERNAME=root
DB_PASSWORD=

# Atau jika pakai user khusus:
# DB_USERNAME=vms_user
# DB_PASSWORD=password123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 3.3 Generate Application Key

```bash
# Generate APP_KEY
php artisan key:generate

# Output: Application key set successfully.
```

---

## 4. SETUP PROJECT VMS

### 4.1 Copy Files Project

**Struktur folder yang harus dicopy ke project Laravel:**

```
VMS/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── DashboardController.php
│   │       ├── RfidScanController.php
│   │       └── VisitorController.php
│   └── Models/
│       ├── User.php
│       ├── RfidCard.php
│       ├── Location.php
│       ├── AccessRight.php
│       └── TrackingLog.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_users_table.php
│       ├── 2024_01_01_000002_create_locations_table.php
│       ├── 2024_01_01_000003_create_rfid_cards_table.php
│       ├── 2024_01_01_000004_create_access_rights_table.php
│       └── 2024_01_01_000005_create_tracking_logs_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── rfid/
│       │   └── scan.blade.php
│       └── visitors/
│           ├── index.blade.php
│           └── create.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── public/
    └── images/
        └── default-avatar.png
```

### 4.2 Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if using Vite/Mix)
npm install

# Compile assets (opsional)
npm run dev
```

### 4.3 Create Storage Link

```bash
# Create symbolic link dari storage ke public
php artisan storage:link

# Output: The [public/storage] link has been connected to [storage/app/public].
```

### 4.4 Set Permissions (Linux/Mac only)

```bash
# Set writable permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Change owner to web server user
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 5. SEEDING DATA (TESTING)

### 5.1 Run Migrations

```bash
# Jalankan semua migrations
php artisan migrate

# Output:
# Migrating: 2024_01_01_000001_create_users_table
# Migrated:  2024_01_01_000001_create_users_table (50.23ms)
# Migrating: 2024_01_01_000002_create_locations_table
# Migrated:  2024_01_01_000002_create_locations_table (45.67ms)
# ... dst
```

**Jika ada error:**
```bash
# Reset database dan migrate ulang
php artisan migrate:fresh
```

### 5.2 Create Seeder

**Buat file seeder:**
```bash
php artisan make:seeder LocationSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder RfidCardSeeder
php artisan make:seeder AccessRightSeeder
```

**Edit `database/seeders/LocationSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            [
                'name' => 'Pintu Utama',
                'code' => 'MAIN',
                'description' => 'Pintu masuk utama kampus',
                'floor' => 'Ground',
                'building' => 'Gedung A',
                'capacity' => null,
                'requires_special_access' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Dekanat',
                'code' => 'DEK',
                'description' => 'Kantor Dekanat',
                'floor' => 'Lantai 2',
                'building' => 'Gedung A',
                'capacity' => 20,
                'requires_special_access' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Aula',
                'code' => 'AULA',
                'description' => 'Aula serba guna',
                'floor' => 'Lantai 1',
                'building' => 'Gedung B',
                'capacity' => 200,
                'requires_special_access' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Lab Informatika',
                'code' => 'LAB-IT',
                'description' => 'Laboratorium komputer',
                'floor' => 'Lantai 3',
                'building' => 'Gedung C',
                'capacity' => 40,
                'requires_special_access' => false,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
```

**Edit `database/seeders/UserSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Ahmad Yani',
                'email' => 'ahmad.yani@example.com',
                'phone' => '081234567890',
                'user_type' => 'employee',
                'address' => 'Jakarta Selatan',
                'employee_id' => 'EMP001',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'phone' => '081234567891',
                'user_type' => 'guest',
                'address' => 'Jakarta Pusat',
                'institution' => 'Universitas Indonesia',
                'is_active' => true,
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra.dewi@example.com',
                'phone' => '081234567892',
                'user_type' => 'employee',
                'address' => 'Jakarta Timur',
                'employee_id' => 'EMP002',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
```

**Edit `database/seeders/RfidCardSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RfidCard;

class RfidCardSeeder extends Seeder
{
    public function run()
    {
        $cards = [
            [
                'uid' => 'E004012345ABCD',
                'user_id' => 1,
                'card_number' => 'CARD-001',
                'status' => 'active',
                'registered_at' => now(),
            ],
            [
                'uid' => 'E004012345ABCE',
                'user_id' => 2,
                'card_number' => 'CARD-002',
                'status' => 'active',
                'registered_at' => now(),
                'expired_at' => now()->addDays(30),
            ],
            [
                'uid' => 'E004012345ABCF',
                'user_id' => 3,
                'card_number' => 'CARD-003',
                'status' => 'active',
                'registered_at' => now(),
            ],
        ];

        foreach ($cards as $card) {
            RfidCard::create($card);
        }
    }
}
```

**Edit `database/seeders/AccessRightSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessRight;

class AccessRightSeeder extends Seeder
{
    public function run()
    {
        $accessRights = [
            // Employee 1 (Ahmad Yani) - Full access
            ['user_id' => 1, 'location_id' => 1, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 2, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 3, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 4, 'can_access' => true, 'access_type' => 'permanent'],
            
            // Guest (Budi Santoso) - Limited access
            ['user_id' => 2, 'location_id' => 1, 'can_access' => true, 'access_type' => 'temporary'],
            ['user_id' => 2, 'location_id' => 3, 'can_access' => true, 'access_type' => 'temporary'],
            ['user_id' => 2, 'location_id' => 2, 'can_access' => false, 'access_type' => 'temporary'], // Denied
            
            // Employee 2 (Citra Dewi) - Partial access
            ['user_id' => 3, 'location_id' => 1, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 3, 'location_id' => 4, 'can_access' => true, 'access_type' => 'permanent'],
        ];

        foreach ($accessRights as $right) {
            AccessRight::create($right);
        }
    }
}
```

**Edit `database/seeders/DatabaseSeeder.php`:**
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            LocationSeeder::class,
            UserSeeder::class,
            RfidCardSeeder::class,
            AccessRightSeeder::class,
        ]);
    }
}
```

### 5.3 Run Seeder

```bash
# Run all seeders
php artisan db:seed

# Output:
# Seeding: Database\Seeders\LocationSeeder
# Seeded:  Database\Seeders\LocationSeeder (15.23ms)
# Seeding: Database\Seeders\UserSeeder
# Seeded:  Database\Seeders\UserSeeder (10.45ms)
# ... dst
```

**Atau migrate + seed sekaligus:**
```bash
php artisan migrate:fresh --seed
```

---

## 6. TESTING RFID

### 6.1 Start Development Server

```bash
# Jalankan Laravel development server
php artisan serve

# Output:
# Starting Laravel development server: http://127.0.0.1:8000
```

**Atau custom port:**
```bash
php artisan serve --port=8080
```

### 6.2 Akses Website

Buka browser dan akses:
- **Dashboard**: http://localhost:8000/dashboard
- **Scan RFID**: http://localhost:8000/scan
- **Daftar Pengunjung**: http://localhost:8000/visitors

### 6.3 Testing Scan RFID

**Skenario 1: Scan dengan RFID Reader Fisik**

1. Buka halaman: http://localhost:8000/scan
2. Pilih lokasi: "Ruang Dekanat"
3. Pastikan cursor di input field
4. Tap kartu RFID ke reader
5. UID otomatis terisi dan terkirim
6. Lihat hasil scan

**Skenario 2: Scan Manual (Tanpa Reader)**

1. Buka halaman: http://localhost:8000/scan
2. Pilih lokasi: "Ruang Dekanat"
3. Ketik manual UID: `E004012345ABCD`
4. Tekan Enter
5. Lihat hasil scan

**Skenario 3: Testing via Postman**

```bash
# Request: POST http://localhost:8000/rfid/scan
# Headers:
Content-Type: application/json
X-CSRF-TOKEN: {{ csrf_token }}

# Body (JSON):
{
    "uid": "E004012345ABCD",
    "location": "DEK"
}

# Expected Response:
{
    "success": true,
    "message": "Scan berhasil",
    "data": {
        "user": {
            "id": 1,
            "name": "Ahmad Yani",
            "email": "ahmad.yani@example.com",
            "type": "Pegawai"
        },
        "location": {
            "id": 2,
            "name": "Ruang Dekanat",
            "code": "DEK"
        },
        "tracking": {
            "action_type": "entry",
            "action_name": "Masuk",
            "status": "accepted",
            "scanned_at": "2025-12-14 10:30:00"
        }
    }
}
```

### 6.4 Test Cases

**Test Case 1: Entry (Scan Pertama)**
```
1. Scan kartu: E004012345ABCD
2. Lokasi: MAIN
3. Expected: action_type = "entry"
4. Expected: status = "accepted"
```

**Test Case 2: Exit (Scan Kedua di Lokasi Sama)**
```
1. Scan kartu: E004012345ABCD
2. Lokasi: MAIN (sama dengan sebelumnya)
3. Expected: action_type = "exit"
```

**Test Case 3: Move (Scan di Lokasi Berbeda)**
```
1. Scan kartu: E004012345ABCD
2. Lokasi: AULA (berbeda dari sebelumnya)
3. Expected: action_type = "move"
```

**Test Case 4: Denied (Tidak Punya Akses)**
```
1. Scan kartu: E004012345ABCE (Budi - guest)
2. Lokasi: DEK (Dekanat - tidak punya akses)
3. Expected: status = "denied"
4. Expected: message = "Anda tidak memiliki hak akses"
```

**Test Case 5: Kartu Tidak Terdaftar**
```
1. Scan kartu: XXXXXXXXXXXXXX (tidak ada di DB)
2. Lokasi: MAIN
3. Expected: HTTP 404
4. Expected: message = "Kartu RFID tidak terdaftar"
```

---

## 7. TROUBLESHOOTING

### 7.1 Error: "Database connection failed"

**Solusi:**
```bash
# 1. Cek MySQL service running
# Windows (XAMPP): Start MySQL di XAMPP Control Panel
# Linux: sudo service mysql start

# 2. Cek credentials di .env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vms_unhan
DB_USERNAME=root
DB_PASSWORD=

# 3. Test koneksi
php artisan tinker
DB::connection()->getPdo();
```

### 7.2 Error: "SQLSTATE[HY000] [2002] Connection refused"

**Solusi:**
```bash
# Pastikan MySQL running dan listening
netstat -an | grep 3306

# Atau coba pakai 127.0.0.1 instead of localhost
DB_HOST=127.0.0.1
```

### 7.3 Error: "Class not found"

**Solusi:**
```bash
# Regenerate autoload
composer dump-autoload

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 7.4 Error: "419 Page Expired" saat POST

**Solusi:**
```javascript
// Pastikan CSRF token ada di request
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

### 7.5 RFID Reader Tidak Terdeteksi

**Solusi:**
1. Cek USB connection (cabut-pasang)
2. Cek Device Manager (Windows) - harus muncul sebagai Keyboard
3. Test di Notepad: tap kartu, UID harus muncul
4. Pastikan reader dalam HID mode

### 7.6 UID Tidak Muncul di Input Field

**Solusi:**
```javascript
// Pastikan input field autofocus
<input type="text" id="rfid-input" autofocus>

// Atau focus manual via JavaScript
$(document).ready(function() {
    $('#rfid-input').focus();
});
```

### 7.7 Slow Query / Performance Issue

**Solusi:**
```bash
# Add indexes ke database
php artisan migrate:refresh

# Enable query log untuk debugging
DB::enableQueryLog();
// ... run query
dd(DB::getQueryLog());
```

---

## 8. MAINTENANCE & BACKUP

### 8.1 Database Backup

```bash
# Backup database
mysqldump -u root -p vms_unhan > backup_vms_$(date +%Y%m%d).sql

# Restore database
mysql -u root -p vms_unhan < backup_vms_20251214.sql
```

### 8.2 Code Deployment

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --no-dev
npm install

# Run migrations
php artisan migrate --force

# Clear cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

### 8.3 Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Clear old logs
php artisan log:clear
```

---

## 9. PRODUCTION DEPLOYMENT (Optional)

### 9.1 Optimize for Production

```bash
# Install dependencies (production)
composer install --optimize-autoloader --no-dev

# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize Composer autoload
composer dump-autoload --optimize
```

### 9.2 Security Checklist

```env
# .env untuk production
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... (generate baru!)

# Gunakan HTTPS
APP_URL=https://vms.unhan.ac.id
```

---

## 10. DOKUMENTASI LENGKAP

File dokumentasi yang tersedia:
- ✅ `DOKUMENTASI_ARSITEKTUR.md` - Penjelasan arsitektur sistem
- ✅ `DOKUMENTASI_INTEGRASI_RFID.md` - Cara kerja RFID USB
- ✅ `VALIDASI_AKADEMIK.md` - Justifikasi simulasi untuk skripsi
- ✅ `PANDUAN_INSTALASI.md` - File ini (step-by-step setup)

---

## 📞 SUPPORT

Jika ada pertanyaan atau masalah:
1. Baca dokumentasi lengkap di folder project
2. Cek Laravel documentation: https://laravel.com/docs
3. Search di Stack Overflow
4. Contact: vms.support@unhan.ac.id (contoh)

---

**Selamat mencoba! Good luck dengan skripsi Anda! 🎓🚀**
