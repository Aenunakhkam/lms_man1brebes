# LMS MAN 1 Brebes

LMS (Learning Management System) ini dikembangkan dengan kerangka kerja Laravel (Backend) dan Vue.js / Vuetify (Frontend) khusus untuk MAN 1 Brebes. Panduan ini akan membantu Anda menginstal dan menjalankan proyek ini di perangkat atau server (hosting) mana pun.

## 🚀 Fitur Utama
- Panel Admin, Guru, dan Siswa
- Manajemen Kelas dan Mata Pelajaran
- Penugasan (Assignment) dan Pengumpulan Tugas
- Sistem Ujian / CBT (Computer Based Test)
- Kehadiran Siswa
- Responsive Design (Bisa diakses dari HP maupun Laptop)

## 📋 Persyaratan Sistem
Pastikan perangkat/server Anda telah menginstal perangkat lunak berikut:
- **PHP** versi 8.2 atau lebih baru
- **Composer** (untuk dependensi PHP)
- **Node.js** & **NPM** (untuk aset Frontend)
- **MySQL** / MariaDB (Database)
- Web Server (Apache/Nginx/Laragon/XAMPP)

---

## 🛠️ Panduan Instalasi (Development / Lokal)

1. **Kloning Repositori**
   Buka terminal/CMD dan jalankan perintah berikut:
   ```bash
   git clone https://github.com/Aenunakhkam/lms_man1brebes.git
   cd lms_man1brebes
   ```

2. **Instal Dependensi PHP & Node**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment (.env)**
   - Salin file `.env.example` menjadi `.env`:
     ```bash
     cp .env.example .env
     ```
   - Buka file `.env` dan atur koneksi database Anda, misalnya:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=lms_man1brebes
     DB_USERNAME=root
     DB_PASSWORD=
     ```
   - **PENTING UNTUK LOGO/FAVICON**: Pastikan variabel `APP_URL` di dalam file `.env` diubah sesuai alamat akses web Anda agar gambar dan logo bisa dimuat dengan benar (misal: `APP_URL=http://localhost:8000` atau `APP_URL=https://lms.sekolah.com`).

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Migrasi Database (Buat Tabel)**
   ```bash
   php artisan migrate
   ```
   *(Opsional)* Jika Anda memiliki file _seeder_ (data dummy), jalankan: `php artisan db:seed`.

6. **Tautkan Storage (Storage Link)**
   Agar file uploap (tugas/materi) dan beberapa aset publik bisa diakses:
   ```bash
   php artisan storage:link
   ```

7. **Compile Aset Frontend (Vue & CSS)**
   ```bash
   npm run build
   ```

8. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```
   Aplikasi sekarang dapat diakses melalui `http://localhost:8000`

---

## 🌐 Panduan Optimasi Hosting (Production)

Saat mengunggah ke server hosting atau CPanel, pastikan untuk menjalankan perintah optimasi ini agar aplikasi menjadi **sangat ringan dan cepat**:

```bash
# Optimasi Autoloader
composer install --optimize-autoloader --no-dev

# Cache Konfigurasi & Rute
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika terjadi masalah pada logo atau gambar yang hilang di server/perangkat baru, lakukan langkah berikut:
1. Periksa kembali file `.env` pada variabel `APP_URL` dan pastikan sesuai dengan URL yang digunakan (misal: `http://localhost:8000` atau domain Anda).
2. Jalankan perintah `php artisan storage:link` (Jika sebelumnya sudah ada folder `public/storage`, hapus terlebih dahulu lalu jalankan perintah ini lagi).
3. Hapus cache konfigurasi dengan menjalankan: `php artisan config:clear` dan `php artisan cache:clear`.

---
**Hak Cipta © 2026 - MAN 1 Brebes**
