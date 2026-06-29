# Troubleshooting Guide - Sistem Penilaian SAW

## Error yang Telah Diperbaiki

### 1. Error: Column not found 'a.applicant_name'
**Masalah**: Query di `getAllApplications()` menggunakan kolom yang tidak ada di tabel `applications`.

**Solusi**: 
- Diperbaiki query untuk menggunakan JOIN dengan tabel `users`
- Menggunakan `u.name as applicant_name` dan `u.email as applicant_email`
- Menambahkan `u.phone as applicant_phone`

**File yang diperbaiki**:
- `includes/job_manager.php` (method `getAllApplications` dan `getApplicationById`)

### 2. Error: array_slice() Argument must be of type array
**Masalah**: Method `getAllApplications()` mengembalikan `false` saat error, tapi kode mengharapkan array.

**Solusi**:
- Menambahkan pengecekan `is_array()` sebelum `array_slice()`
- Menggunakan array kosong `[]` sebagai fallback

**File yang diperbaiki**:
- `admin/dashboard.php`

### 3. Error: Kolom tidak ada (applicant_age, applicant_gender, work_experience)
**Masalah**: Template menggunakan kolom yang tidak ada di database.

**Solusi**:
- Menghapus referensi ke kolom yang tidak ada
- Menggunakan `applicant_phone` sebagai gantinya
- Memperbaiki JavaScript di modal detail

**File yang diperbaiki**:
- `admin/applications.php`
- `hrd/applications.php`

## Cara Testing

### 1. Test Database Connection
Jalankan: `http://localhost/rekruter/test_evaluation.php`

### 2. Test Update Database
Jalankan: `http://localhost/rekruter/update_database.php`

### 3. Test Manual
1. Login sebagai Admin/HRD
2. Buka halaman Aplikasi
3. Coba berikan penilaian
4. Lihat hasil ranking
5. Login sebagai Pelamar dan lihat hasil

## Error Umum dan Solusi

### Database Connection Error
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'
```
**Solusi**: Periksa konfigurasi database di `config/database.php`

### Table doesn't exist
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'rekruter_db.evaluation_criteria' doesn't exist
```
**Solusi**: Jalankan `update_database.php` untuk membuat tabel baru

### Permission Denied
```
Fatal error: Uncaught Error: Call to undefined function isLoggedIn()
```
**Solusi**: Pastikan `includes/auth.php` di-include dengan benar

### SAW Score tidak muncul
**Kemungkinan penyebab**:
1. Belum semua kriteria dinilai
2. Total bobot kriteria tidak 100%
3. Error dalam perhitungan

**Solusi**:
1. Pastikan semua kriteria sudah dinilai
2. Klik "Hitung Ulang Ranking"
3. Periksa log error di browser console

## File Penting

### Database
- `config/database.sql` - Schema database
- `config/database.php` - Konfigurasi koneksi

### Backend Classes
- `includes/evaluation_manager.php` - Logic penilaian SAW
- `includes/job_manager.php` - Management aplikasi dan job
- `includes/auth.php` - Authentication

### Frontend Pages
- `admin/evaluate_application.php` - Form penilaian
- `admin/ranking.php` - Hasil ranking
- `applicant/evaluation_results.php` - Hasil untuk pelamar

### Utility Files
- `update_database.php` - Update schema database
- `test_evaluation.php` - Test semua sistem

## Debugging Tips

### 1. Enable Error Reporting
Tambahkan di awal file PHP:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 2. Check Database Queries
Tambahkan logging di method database:
```php
error_log("Query: " . $query);
error_log("Error: " . $e->getMessage());
```

### 3. Browser Console
Buka Developer Tools (F12) untuk melihat JavaScript errors

### 4. PHP Error Log
Periksa file error log PHP di:
- Windows XAMPP: `C:\xampp\php\logs\php_error_log`
- Linux: `/var/log/apache2/error.log`

## Backup dan Recovery

### Backup Database
```sql
mysqldump -u root -p rekruter_db > backup.sql
```

### Restore Database
```sql
mysql -u root -p rekruter_db < backup.sql
```

### Backup Files
Backup folder `rekruter` secara berkala, terutama:
- `config/`
- `includes/`
- `uploads/`

## Performance Tips

### 1. Database Indexing
Pastikan ada index pada kolom yang sering di-query:
```sql
CREATE INDEX idx_applications_user_id ON applications(user_id);
CREATE INDEX idx_applications_job_id ON applications(job_id);
CREATE INDEX idx_evaluations_application_id ON application_evaluations(application_id);
```

### 2. Caching
Implementasi caching untuk hasil ranking yang tidak berubah sering.

### 3. Pagination
Untuk data besar, implementasi pagination pada daftar aplikasi.

## Security Checklist

- ✅ Validasi input pada semua form
- ✅ Sanitasi output dengan `htmlspecialchars()`
- ✅ Prepared statements untuk query database
- ✅ Session management yang aman
- ✅ Role-based access control
- ✅ File upload validation

## Kontak Support

Jika masih ada masalah, periksa:
1. File log error
2. Browser console
3. Database connection
4. File permissions
5. PHP version compatibility (minimum PHP 7.4)
