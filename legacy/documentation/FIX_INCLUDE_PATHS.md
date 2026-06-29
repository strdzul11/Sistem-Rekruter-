# Fix Include Paths - Troubleshooting Guide

## Masalah yang Diperbaiki

### Error: Failed to open stream 'database.php'
```
Warning: require_once(database.php): Failed to open stream: No such file or directory
```

**Penyebab:** Path include yang salah di file `includes/evaluation_manager.php`

**Solusi:** Menggunakan `__DIR__` untuk path absolut yang benar

## Perbaikan yang Dilakukan

### 1. File: `includes/evaluation_manager.php`
**Sebelum:**
```php
<?php
require_once 'database.php';
```

**Sesudah:**
```php
<?php
require_once __DIR__ . '/../config/database.php';
```

### 2. Struktur Directory yang Benar
```
rekruter/
├── config/
│   ├── database.php ✓
│   └── database.sql ✓
├── includes/
│   ├── auth.php ✓
│   ├── evaluation_manager.php ✓ (DIPERBAIKI)
│   └── job_manager.php ✓
├── admin/
│   ├── evaluate_application.php ✓
│   └── ranking.php ✓
└── hrd/
    ├── evaluate_application.php ✓
    └── ranking.php ✓
```

## Path Include yang Benar

### Dari folder `includes/` ke `config/`:
```php
require_once __DIR__ . '/../config/database.php';
```

### Dari folder `admin/` ke `includes/`:
```php
require_once '../includes/evaluation_manager.php';
```

### Dari folder `hrd/` ke `includes/`:
```php
require_once '../includes/evaluation_manager.php';
```

## Testing

### 1. Test Include Files
Jalankan: `http://localhost/rekruter/test_include.php`

### 2. Test Evaluation System
Jalankan: `http://localhost/rekruter/test_evaluation.php`

### 3. Test Manual
1. Login sebagai Admin
2. Buka: `http://localhost/rekruter/admin/evaluate_application.php?id=1`
3. Pastikan tidak ada error include

## Troubleshooting Lanjutan

### Jika masih error include:

1. **Periksa Case Sensitivity** (Linux/Mac):
   - Pastikan nama file sesuai: `database.php` bukan `Database.php`

2. **Periksa Permissions** (Linux/Mac):
   ```bash
   chmod 644 config/database.php
   chmod 644 includes/evaluation_manager.php
   ```

3. **Periksa PHP Error Log**:
   - Windows XAMPP: `C:\xampp\php\logs\php_error_log`
   - Linux: `/var/log/apache2/error.log`

4. **Enable Error Reporting**:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

### Jika masih error database connection:

1. **Periksa MySQL Service**:
   - XAMPP Control Panel → Start MySQL

2. **Periksa Database Config**:
   ```php
   // config/database.php
   private $host = 'localhost';
   private $db_name = 'rekruter_db';
   private $username = 'root';
   private $password = '';
   ```

3. **Test Database Connection**:
   ```php
   $database = new Database();
   $db = $database->getConnection();
   if ($db) {
       echo "Database connected!";
   }
   ```

## File yang Telah Diperbaiki

- ✅ `includes/evaluation_manager.php` - Path include diperbaiki
- ✅ `test_include.php` - File test untuk debugging
- ✅ Semua file lain sudah menggunakan path yang benar

## Status

🟢 **RESOLVED** - Include path error telah diperbaiki

Sistem penilaian SAW sekarang dapat diakses tanpa error include.
