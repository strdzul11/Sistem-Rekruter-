# Website Rekrutmen - Rekruter

Website rekrutmen modern dengan sistem manajemen lengkap untuk Admin, HRD, dan Pelamar.

## Fitur Utama

### 🔐 Sistem Autentikasi
- Login dengan email dan password
- Registrasi untuk pelamar baru
- Session management yang aman
- Role-based access control (Admin, HRD, Applicant)

### 👨‍💼 Admin Dashboard
- **Manajemen User**: Kelola semua user dalam sistem
- **Manajemen Lowongan**: Buat, edit, dan hapus lowongan kerja
- **Manajemen Aplikasi**: Review dan update status aplikasi
- **Dashboard Analytics**: Statistik lengkap dengan chart
- **Laporan**: Monitoring performa sistem

### 👩‍💼 HRD Dashboard
- **Monitoring Aplikasi**: Review aplikasi pelamar
- **Manajemen Lowongan**: Buat dan kelola lowongan kerja
- **Filter Aplikasi**: Filter berdasarkan status dan posisi
- **Update Status**: Update status aplikasi (pending, reviewed, accepted, rejected, interview_scheduled)

### 👤 Applicant Dashboard
- **Browse Lowongan**: Lihat semua lowongan kerja yang tersedia
- **Detail Lowongan**: Informasi lengkap tentang posisi dan persyaratan
- **Apply Job**: Kirim aplikasi dengan surat lamaran
- **Track Application**: Pantau status aplikasi yang dikirim

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Tailwind CSS
- **Icons**: Font Awesome
- **Charts**: Chart.js
- **Development Environment**: XAMPP

## Struktur Database

### Tabel Users
- `id` - Primary Key
- `name` - Nama lengkap
- `email` - Email (unique)
- `password` - Password ter-hash
- `role` - Role (admin, hrd, applicant)
- `phone` - Nomor telepon
- `address` - Alamat
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate

### Tabel Job Listings
- `id` - Primary Key
- `position` - Posisi pekerjaan
- `company` - Nama perusahaan
- `location` - Lokasi kerja
- `description` - Deskripsi pekerjaan
- `requirements` - Persyaratan
- `salary_range` - Range gaji
- `employment_type` - Jenis pekerjaan (full-time, part-time, contract, internship)
- `status` - Status (active, inactive, closed)
- `created_by` - ID user yang membuat
- `created_at` - Tanggal dibuat
- `updated_at` - Tanggal diupdate

### Tabel Applications
- `id` - Primary Key
- `user_id` - ID pelamar
- `job_id` - ID lowongan
- `cover_letter` - Surat lamaran
- `resume_path` - Path file CV
- `status` - Status aplikasi (pending, reviewed, accepted, rejected, interview_scheduled)
- `applied_at` - Tanggal apply
- `updated_at` - Tanggal diupdate

## Instalasi

### 1. Persiapan Environment
```bash
# Install XAMPP
# Download dari https://www.apachefriends.org/
# Start Apache dan MySQL services
```

### 2. Setup Database
```bash
# Buka phpMyAdmin (http://localhost/phpmyadmin)
# Import file database.sql
# Database akan dibuat otomatis dengan data sample
```

### 3. Konfigurasi
```bash
# Edit file config/database.php jika diperlukan
# Sesuaikan host, username, password database
```

### 4. Akses Website
```bash
# Buka browser dan akses:
http://localhost/rekruter/
```

## Akun Demo

### Admin
- **Email**: admin@rekruter.com
- **Password**: password

### HRD
- **Email**: hr@rekruter.com
- **Password**: password

### Applicant
- Daftar akun baru melalui halaman registrasi

## Struktur File

```
rekruter/
├── auth/
│   ├── login.php
│   ├── login_process.php
│   ├── register.php
│   ├── register_process.php
│   └── logout.php
├── admin/
│   ├── dashboard.php
│   ├── users.php
│   ├── jobs.php
│   └── applications.php
├── hrd/
│   ├── dashboard.php
│   ├── applications.php
│   └── jobs.php
├── applicant/
│   └── dashboard.php
├── config/
│   └── database.php
├── includes/
│   ├── auth.php
│   └── job_manager.php
├── database.sql
├── index.php
└── README.md
```

## Fitur Responsive Design

- **Mobile First**: Design yang optimal untuk mobile
- **Responsive Grid**: Layout yang adaptif untuk semua ukuran layar
- **Touch Friendly**: Interface yang mudah digunakan di touchscreen
- **Modern UI**: Clean dan minimalist design dengan Tailwind CSS

## Keamanan

- **Password Hashing**: Menggunakan PHP password_hash()
- **SQL Injection Protection**: Prepared statements dengan PDO
- **XSS Protection**: Input sanitization dan output escaping
- **Session Security**: Secure session management
- **Role-based Access**: Kontrol akses berdasarkan role user

## Pengembangan Lebih Lanjut

### Fitur yang Dapat Ditambahkan:
- Upload CV/Resume
- Email notifications
- Advanced search dan filter
- Interview scheduling
- Company profiles
- Job categories
- Skills matching
- Analytics dashboard
- API endpoints
- Mobile app integration

### Optimisasi:
- Caching system
- Database indexing
- Image optimization
- CDN integration
- Performance monitoring

## Kontribusi

1. Fork repository
2. Buat feature branch
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

MIT License - bebas digunakan untuk keperluan komersial dan non-komersial.

## Support

Untuk pertanyaan atau bantuan, silakan buat issue di repository ini.

---

**Dibuat dengan ❤️ untuk memudahkan proses rekrutmen**
