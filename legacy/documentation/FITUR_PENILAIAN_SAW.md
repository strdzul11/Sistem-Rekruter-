# Fitur Penilaian dan Ranking menggunakan Metode SAW

## Deskripsi
Fitur ini menambahkan sistem penilaian dan perankingan pelamar menggunakan metode SAW (Simple Additive Weighting) pada sistem rekrutmen PT. Clarajob.

## Fitur yang Ditambahkan

### 1. Database
- **evaluation_criteria**: Tabel untuk menyimpan kriteria penilaian dan bobotnya
- **application_evaluations**: Tabel untuk menyimpan penilaian setiap aplikasi
- **application_rankings**: Tabel untuk menyimpan hasil ranking SAW

### 2. Backend (PHP Classes)
- **EvaluationManager**: Class untuk mengelola penilaian dan perhitungan SAW
- **CriteriaManager**: Class untuk mengelola kriteria penilaian

### 3. Halaman Admin
- **evaluate_application.php**: Halaman untuk memberikan penilaian pada aplikasi
- **ranking.php**: Halaman untuk melihat hasil ranking pelamar
- **get_evaluation_detail.php**: API untuk mendapatkan detail penilaian

### 4. Halaman HRD
- **evaluate_application.php**: Halaman untuk memberikan penilaian pada aplikasi
- **ranking.php**: Halaman untuk melihat hasil ranking pelamar
- **get_evaluation_detail.php**: API untuk mendapatkan detail penilaian

### 5. Halaman Pelamar
- **evaluation_results.php**: Halaman untuk melihat hasil penilaian
- **get_detailed_evaluation.php**: API untuk mendapatkan detail penilaian pribadi

## Cara Kerja Metode SAW

### 1. Kriteria Penilaian Default
- **Pendidikan** (20%): Tingkat pendidikan dan relevansi dengan posisi
- **Pengalaman Kerja** (25%): Lama dan relevansi pengalaman kerja
- **Keterampilan Teknis** (30%): Kemampuan teknis sesuai kebutuhan posisi
- **Komunikasi** (15%): Kemampuan komunikasi dan presentasi
- **Kepribadian** (10%): Kesesuaian kepribadian dengan budaya perusahaan

### 2. Proses Perhitungan SAW
1. **Normalisasi**: Setiap skor kriteria dibagi dengan nilai maksimum (5)
2. **Pembobotan**: Skor ternormalisasi dikalikan dengan bobot kriteria
3. **Penjumlahan**: Semua hasil pembobotan dijumlahkan untuk mendapat skor SAW
4. **Ranking**: Pelamar diurutkan berdasarkan skor SAW tertinggi

### 3. Formula SAW
```
SAW Score = Σ(Normalized Score × Weight)
```

Dimana:
- Normalized Score = Score / Max Value
- Weight = Bobot kriteria dalam persen

## Instalasi

### 1. Update Database
Jalankan file `update_database.php` untuk membuat tabel baru:
```
http://localhost/rekruter/update_database.php
```

### 2. File yang Ditambahkan
```
includes/evaluation_manager.php
admin/evaluate_application.php
admin/ranking.php
admin/get_evaluation_detail.php
hrd/evaluate_application.php
hrd/ranking.php
hrd/get_evaluation_detail.php
applicant/evaluation_results.php
applicant/get_detailed_evaluation.php
update_database.php
```

## Cara Penggunaan

### Untuk Admin/HRD:
1. Login ke sistem sebagai Admin atau HRD
2. Buka halaman "Aplikasi"
3. Klik ikon bintang (⭐) untuk memberikan penilaian
4. Isi skor untuk setiap kriteria (1-5)
5. Tambahkan catatan jika diperlukan
6. Simpan penilaian
7. Klik ikon trofi (🏆) untuk melihat ranking

### Untuk Pelamar:
1. Login ke sistem sebagai Pelamar
2. Klik "Hasil Penilaian" di menu navigasi
3. Lihat skor SAW dan ranking Anda
4. Klik "Lihat Detail Penilaian" untuk melihat rincian per kriteria

## Fitur Tambahan

### 1. Ranking Otomatis
- Ranking dihitung ulang secara otomatis setiap kali ada penilaian baru
- Pelamar diurutkan berdasarkan skor SAW tertinggi

### 2. Visualisasi Skor
- Progress bar untuk menampilkan persentase skor
- Indikator warna untuk kategori performa (Sangat Baik, Baik, Cukup)
- Grafik kontribusi setiap kriteria terhadap skor total

### 3. Detail Penilaian
- Rincian skor per kriteria
- Informasi penilai
- Catatan penilaian
- Penjelasan metode SAW

### 4. Statistik
- Total pelamar
- Jumlah yang sudah dinilai
- Jumlah yang sudah diranking

## Keamanan
- Validasi role user untuk setiap halaman
- Pelamar hanya bisa melihat hasil penilaian mereka sendiri
- Admin dan HRD dapat menilai semua aplikasi
- Proteksi CSRF pada form penilaian

## Teknologi yang Digunakan
- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3 (Tailwind CSS), JavaScript
- **Icons**: Font Awesome 6
- **Database**: MySQL dengan PDO

## Troubleshooting

### Error Database
Jika terjadi error database, pastikan:
1. Database `rekruter_db` sudah dibuat
2. Jalankan `update_database.php` untuk membuat tabel baru
3. Periksa koneksi database di `config/database.php`

### Error Permission
Jika terjadi error akses:
1. Pastikan user sudah login dengan role yang benar
2. Periksa session PHP aktif
3. Clear browser cache dan cookies

### Error Perhitungan SAW
Jika ranking tidak muncul:
1. Pastikan semua kriteria sudah dinilai
2. Periksa total bobot kriteria = 100%
3. Jalankan "Hitung Ulang Ranking" di halaman ranking

## Pengembangan Selanjutnya
- Tambah kriteria penilaian dinamis
- Export hasil ranking ke PDF/Excel
- Notifikasi email untuk pelamar
- Dashboard analytics untuk HR
- Integrasi dengan sistem payroll
