# Rekruter - Sistem Rekrutmen & Penilaian Pelamar (SAW & Google Calendar Integration)

**Rekruter** adalah platform manajemen rekrutmen terintegrasi yang dirancang untuk merampingkan proses seleksi kandidat secara profesional. Sistem ini menyediakan alur kerja yang mudah digunakan bagi **Admin**, **HRD**, dan **Pelamar**, dilengkapi dengan sistem penilaian otomatis berbasis metode **Simple Additive Weighting (SAW)**, integrasi **Google Calendar** untuk penjadwalan interview, pengiriman surat otomatis berformat **PDF**, serta fitur **Quick Apply** tanpa login bagi pelamar cepat.

---

## 🌟 Fitur Utama

### 1. Sistem Perankingan Metode SAW (Simple Additive Weighting)
* **Kriteria Dinamis & Terbobot**: Mendukung penilaian berdasarkan kriteria default seperti *Pendidikan* (20%), *Pengalaman Kerja* (25%), *Keterampilan Teknis* (30%), *Komunikasi* (15%), dan *Kepribadian* (10%).
* **Normalisasi Otomatis**: Menghitung matriks keputusan ternormalisasi secara *real-time* berdasarkan nilai tertinggi setiap kriteria.
* **Perankingan Real-time**: Mengurutkan pelamar berdasarkan skor preferensi SAW tertinggi untuk membantu HRD mengambil keputusan objektif.

### 2. Integrasi Google Calendar & Self-Scheduling
* **Jadwal Interaktif**: HRD dapat membuat opsi slot waktu interview langsung dari panel sistem.
* **Penjadwalan Mandiri**: Pelamar dapat memilih slot waktu interview sendiri sesuai ketersediaan mereka.
* **Sinkronisasi Otomatis**: Integrasi penuh dengan Google Calendar API menggunakan Google OAuth 2.0 untuk menyisipkan jadwal secara otomatis ke kalender HRD maupun pelamar.

### 3. Quick Apply & Lacak Status Tanpa Login
* **Pendaftaran Cepat**: Pelamar dapat melamar lowongan pekerjaan secara instan tanpa perlu registrasi akun terlebih dahulu.
* **Token Akses Unik**: Setelah melamar, pelamar menerima token unik melalui email/halaman sukses yang dapat digunakan untuk:
  * Memantau perkembangan status aplikasi lamaran.
  * Memilih slot interview yang dijadwalkan oleh HRD.

### 4. Surat Undangan & Penawaran PDF Otomatis
* **Template Surat Dinamis**: Admin dapat mengelola dan membuat template surat undangan interview, penerimaan (*offering letter*), atau penolakan dengan placeholder variabel dinamis.
* **Unduh PDF**: HRD dapat secara instan menerbitkan dan mengunduh surat berformat PDF menggunakan modul `barryvdh/laravel-dompdf`.

### 5. Manajemen Sistem & Keamanan (Panel Admin)
* **Manajemen User & Role**: Pengelolaan data pengguna beserta hak akses (Admin, HRD, Pelamar).
* **Backup & Restore**: Modul untuk melakukan backup database secara manual dan otomatis, serta melakukan pemulihan (*restore*) langsung dari aplikasi.
* **Audit Logs**: Sistem log aktivitas yang merekam setiap tindakan sensitif yang dilakukan oleh pengguna untuk keperluan keamanan dan audit.
* **Konfigurasi SMTP**: Pengaturan pengiriman email sistem beserta utilitas uji coba koneksi SMTP.

---

## 🛠️ Teknologi yang Digunakan

* **Backend**: Laravel framework (versi terbaru, didukung PHP >= 8.3)
* **Database**: MySQL / MariaDB (mendukung SQLite untuk testing)
* **Frontend**: Tailwind CSS & Vanilla JavaScript
* **Build Tools**: Vite & NPM
* **Libraries Utama**:
  * `barryvdh/laravel-dompdf` (Generasi dokumen PDF)
  * `google/apiclient` (Integrasi Google OAuth & Google Calendar API)
  * `laravel/breeze` (Autentikasi dasar dan starter layout)

---

## 🔑 Akun Bawaan (Default Credentials)

Jalankan seeder bawaan untuk menggunakan akun-akun uji coba berikut:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Administrator** | `admin@clarajob.co.id` | `password` |
| **HR Manager** | `hr@clarajob.co.id` | `password` |

---

## 🚀 Panduan Instalasi & Setup

### 1. Prasyarat Sistem
* PHP >= 8.3
* Composer
* Node.js & NPM
* MySQL / MariaDB

### 2. Langkah-Langkah Instalasi
1. **Clone Repositori**:
   ```bash
   git clone https://github.com/strdzul11/sistem-rekruter-.git
   cd sistem-rekruter-
   ```

2. **Instal Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Salin File Konfigurasi**:
   ```bash
   copy .env.example .env
   ```

4. **Konfigurasi Database & SMTP**:
   Buka file `.env` dan sesuaikan koneksi database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rekruter
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```

7. **Instal Dependensi Frontend & Compile Aset**:
   ```bash
   npm install
   npm run build
   ```

8. **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser di: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 📅 Konfigurasi Google Calendar API

Agar integrasi penjadwalan interview berfungsi, Anda perlu mengaktifkan Google Calendar API di Google Cloud Console:

1. Buka [Google Cloud Console](https://console.cloud.google.com/).
2. Buat project baru dan aktifkan **Google Calendar API**.
3. Buka menu **Credentials** dan buat **OAuth 2.0 Client ID**.
4. Tambahkan URI pengalihan berikut pada **Authorized redirect URIs**:
   ```text
   http://127.0.0.1:8000/calendar/google/callback
   ```
5. Salin Client ID dan Client Secret yang didapat, lalu masukkan ke dalam file `.env`:
   ```env
   GOOGLE_CLIENT_ID=isi_client_id_anda
   GOOGLE_CLIENT_SECRET=isi_client_secret_anda
   GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/calendar/google/callback
   ```

---

## 📂 Struktur Direktori Proyek

* `app/` - Logika inti aplikasi (Model, Controller, Service, Middleware, Mail, Jobs).
* `database/` - Migrasi, database seeders, dan factories.
* `resources/` - Aset frontend (CSS, JS) dan file template blade view.
* `routes/` - Pengaturan routing aplikasi (`web.php`, `auth.php`, `console.php`).
* `legacy/` - Kumpulan berkas kode php legacy yang berisi dokumentasi sistem awal serta skrip migrasi SQL lama.
* `public/` - Direktori root publik server web yang berisi file `index.php` dan aset statis terkompilasi.

