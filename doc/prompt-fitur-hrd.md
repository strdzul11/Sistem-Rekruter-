# Prompt Implementasi Fitur HRD — Sistem Rekruter

Setiap bagian di bawah adalah **prompt yang bisa langsung di-paste** ke Claude Code (atau sesi chat lain yang punya akses ke repo `rekruter`) untuk mengerjakan satu fitur. Kerjakan satu per satu — jangan gabung semua sekaligus — supaya mudah di-review dan di-test.

Semua prompt sudah disesuaikan dengan struktur project yang sebenarnya: nama tabel, model, controller, konvensi route (`role:hrd`, `role:admin`, `role:applicant`), dan pola Blade yang sudah dipakai di project ini.

---

## 1. Self-Schedule Interview oleh Kandidat

```
Tambahkan fitur self-schedule interview di project Laravel "rekruter" ini.

KONTEKS SAAT INI:
- Tabel `interview_schedules` sudah ada (migration: 2026_06_27_000007_create_interview_schedules_table.php)
  dengan kolom: application_id, interviewer_id, interview_type, interview_date, timezone,
  duration_minutes, meeting_link, meeting_room, status (scheduled/confirmed/completed/cancelled/rescheduled), notes.
- App\Http\Controllers\HRD\InterviewController dan Admin\InterviewController saat ini HANYA punya method index().
  Tidak ada create/store untuk menjadwalkan interview.
- Kandidat terdaftar (role applicant) punya route /applicant/interviews yang sekarang cuma return view
  statis `applicant.interviews` tanpa controller/data nyata.
- Kandidat quick-apply (tanpa akun, user_id null di tabel applications) cek status lewat
  route publik /check-status (JobController@checkStatus & @searchStatus).

YANG HARUS DIBUAT:
1. Migration: tambah kolom ke `interview_schedules`:
   - `is_proposed` (boolean, default true) — true selama masih berupa opsi slot yang ditawarkan,
     jadi false begitu kandidat memilih satu slot.
   - `selection_token` (string, nullable, unique) — token acak untuk kandidat quick-apply yang
     tidak punya akun, dipakai sebagai akses aman ke halaman pilih jadwal tanpa login.

2. HRD\InterviewController: tambahkan method create(Application $application), store(Request $request).
   - Form: HRD pilih application, interview_type, duration_minutes, lalu input BEBERAPA opsi
     tanggal/jam (minimal 2, gunakan repeater field sederhana di Blade).
   - store() membuat banyak baris InterviewSchedule (satu per opsi) dengan is_proposed=true,
     status='scheduled', dan selection_token diisi HANYA jika application->user_id null.
   - Update applications.interview_status jadi 'scheduled'.

3. Endpoint pemilihan slot oleh kandidat (TANPA notifikasi email karena sistem ini belum punya
   infrastruktur email/notifikasi — fitur itu di luar scope prompt ini):
   - Untuk kandidat login (role applicant): buat Applicant\InterviewController@index yang
     menampilkan data InterviewSchedule asli milik user (ganti view statis), dan
     @select(InterviewSchedule $slot) untuk memilih.
   - Untuk kandidat quick-apply: tambahkan section "Pilih Jadwal Interview" di halaman hasil
     /check-status (jobs/check_status.blade.php) ketika status aplikasi interview_scheduled dan
     ada slot is_proposed=true. Submit via selection_token (route tanpa middleware auth, validasi
     token cocok dengan application yang sedang dilihat).

4. Logic pemilihan (taruh di service class App\Services\InterviewSchedulingService agar tidak
   duplikat antara controller applicant & flow quick-apply):
   - Slot yang dipilih: is_proposed=false, status='confirmed'.
   - Slot-slot lain untuk application_id yang sama: status='cancelled' (jangan dihapus, untuk audit).
   - applications.interview_status = 'scheduled' tetap, applications.status = 'interview_scheduled'.

5. Tambahkan validasi: kandidat tidak bisa memilih slot yang interview_date-nya sudah lewat,
   dan tidak bisa memilih slot yang application_id-nya bukan miliknya.

Ikuti gaya kode yang sudah ada di HRD\JobController dan HRD\EvaluationController (validasi pakai
$request->validate(), redirect()->with('success', ...), Tailwind class yang sama dengan
resources/views/hrd/jobs/_form.blade.php). Tulis migration baru, jangan ubah migration lama
yang sudah pernah dijalankan.
```

---

## 2. Bulk Actions (Terima/Tolak Massal)

```
Tambahkan fitur bulk action di halaman daftar lamaran HRD pada project Laravel "rekruter".

KONTEKS SAAT INI:
- resources/views/hrd/applications/index.blade.php menampilkan semua lamaran tanpa checkbox
  dan tanpa aksi massal.
- App\Http\Controllers\HRD\ApplicationController@update hanya mengubah status SATU application
  sekaligus, validasi: status in:pending,reviewed,accepted,rejected,interview_scheduled.

YANG HARUS DIBUAT:
1. Route baru di routes/web.php, dalam group role:hrd yang sudah ada:
   Route::post('/applications/bulk-update', [HRD\ApplicationController::class, 'bulkUpdate'])
       ->name('applications.bulkUpdate');

2. HRD\ApplicationController@bulkUpdate(Request $request):
   - Validasi: application_ids => required|array|min:1, application_ids.* => exists:applications,id,
     status => required|in:pending,reviewed,accepted,rejected,interview_scheduled.
   - Update dengan Application::whereIn('id', $validated['application_ids'])->update(['status' => $validated['status']]).
   - Redirect balik ke hrd.applications.index dengan flash message yang menyebutkan JUMLAH
     lamaran yang berhasil diubah, contoh: "12 lamaran berhasil diubah menjadi 'accepted'."

3. UI di hrd/applications/index.blade.php:
   - Checkbox di setiap baris + checkbox "pilih semua" di header tabel (pakai Alpine.js,
     project ini sudah pakai Alpine untuk komponen dropdown di components/dropdown.blade.php
     jadi sudah tersedia).
   - Toolbar aksi massal yang muncul/disable otomatis berdasarkan jumlah baris terpilih
     (x-show="selected.length > 0" ala Alpine), berisi dropdown pilih status baru + tombol "Terapkan".
   - Sebelum submit, tampilkan konfirmasi (pakai components/modal.blade.php yang sudah ada di
     project, jangan pakai window.confirm biasa supaya konsisten dengan UI lain) yang
     menyebutkan jumlah lamaran yang akan terdampak.

4. Tambahkan guard sederhana: jika HRD memilih status 'accepted' untuk application yang BELUM
   punya hasil evaluasi/ranking (cek relasi evaluations()->doesntExist()), tampilkan warning
   di toolbar (bukan blocking, cukup info) karena keputusan terima sebaiknya berbasis skor SAW.

Ikuti pola validasi dan response yang sudah dipakai di ApplicationController@update yang
sudah ada (jangan ubah method update() yang lama, bulkUpdate() adalah method baru terpisah).
```

---

## 3. Filter & Pencarian Lanjutan

```
Tambahkan filter dan pencarian lanjutan di halaman lamaran dan ranking untuk role HRD pada
project Laravel "rekruter".

KONTEKS SAAT INI:
- HRD\ApplicationController@index: Application::with(['user','jobListing'])->orderByDesc('created_at')->get()
  — TANPA filter, TANPA pagination (semua data ditarik sekaligus, akan jadi masalah performa
  begitu jumlah lamaran banyak).
- HRD\RankingController@index: ApplicationRanking::with(['application.user','jobListing'])
  ->orderBy('job_id')->orderBy('rank_position')->get() — juga tanpa filter/pagination.
- Tabel applications punya kolom: status, job_id, applicant_name, applicant_email, created_at.
- Tabel application_rankings punya kolom: job_id, saw_score, rank_position, evaluation_status (draft/final).

YANG HARUS DIBUAT:

1. HRD\ApplicationController@index — tambahkan filter via query string, semua opsional:
   - status (select: pending/reviewed/accepted/rejected/interview_scheduled)
   - job_id (select dari JobListing::pluck('position','id'))
   - date_from, date_to (filter created_at, pakai whereBetween/where >=,<=)
   - q (search bebas: cocokkan ke applicant_name, applicant_email, ATAU nama/email user terkait
     via whereHas('user', fn($qq) => ...) supaya jalan baik untuk applicant_type registered
     maupun quick_apply)
   Gunakan Eloquent ->when() untuk setiap filter supaya query tetap bersih. Tambahkan
   ->paginate(20)->withQueryString() di akhir (ganti dari ->get()).

2. HRD\RankingController@index — tambahkan filter:
   - job_id (select)
   - evaluation_status (draft/final)
   - min_score (filter saw_score >= nilai yang diinput, dalam skala 0-1 sesuai cara hitung SAW
     yang sudah ada)
   Tambahkan juga ->paginate(20)->withQueryString().

3. UI filter bar di hrd/applications/index.blade.php dan hrd/rankings/index.blade.php:
   - Form GET (bukan POST, supaya bisa di-bookmark/share dan kompatibel dengan pagination link)
   - Semua input "sticky" (value tetap terisi sesuai request()->get('field') setelah submit)
   - Tombol "Reset Filter" yang mengarah ke route index tanpa query string.
   - Style mengikuti Tailwind yang sudah dipakai di komponen lain (text-input, input-label
     dari resources/views/components/).

4. Pastikan link pagination (di bawah tabel) ikut membawa query string filter — pakai
   withQueryString() seperti disebut di atas, ini bawaan Laravel jadi otomatis berfungsi
   asal blade pagination link-nya default ({{ $applications->links() }}).

Jangan ubah relasi/Eloquent model yang sudah ada di App\Models\Application dan
App\Models\ApplicationRanking, cukup tambahkan scope/where di controller.
```

---

## 4. Perbaikan & Agregasi Multi-Evaluator pada Skor SAW

```
Perbaiki logic perhitungan skor SAW (Simple Additive Weighting) di project Laravel "rekruter"
agar mendukung multi-evaluator dengan benar. ADA BUG yang harus diperbaiki, bukan cuma
penambahan fitur — baca seksi "BUG YANG DITEMUKAN" sebelum mengubah apa pun.

KONTEKS SAAT INI:
- Logic SAW ada di DUA tempat yang isinya identik (duplikat):
  app/Http/Controllers/Admin/EvaluationController.php dan
  app/Http/Controllers/HRD/EvaluationController.php, masing-masing punya private method
  calculateSAWScore() dan calculateJobRankings().
- Tabel application_evaluations punya unique key (application_id, criteria_id, evaluator_id)
  — artinya skema SUDAH mendukung banyak evaluator menilai criteria yang sama untuk
  application yang sama, tiap evaluator punya barisnya sendiri.
- Tabel evaluation_criteria punya kolom `type` (enum: benefit/cost) yang seharusnya menentukan
  arah normalisasi nilai pada SAW (benefit = score/max, cost = min/score), tapi kolom ini
  TIDAK PERNAH dibaca di calculateSAWScore().

BUG YANG DITEMUKAN (perbaiki keduanya):
1. Di calculateSAWScore(), variabel $totalWeight dihitung tapi TIDAK PERNAH dipakai —
   fungsi langsung `return $totalScore` tanpa membaginya dengan $totalWeight. Akibatnya kalau
   ada criteria yang belum dinilai untuk suatu application, skor tidak ternormalisasi dengan benar.
2. Karena query mengambil SEMUA baris application_evaluations untuk suatu application_id
   tanpa mem-filter/group per evaluator, jika 2 evaluator menilai criteria yang SAMA, kontribusi
   criteria itu ke totalScore (dan ke weight) ikut DOUBLE — bukan dirata-rata. Ini membuat skor
   akhir bias ke arah application yang dinilai oleh lebih banyak evaluator, bukan ke kualitas
   kandidat.
3. Kolom evaluation_criteria.type (benefit/cost) tidak pernah dipakai — semua criteria
   diperlakukan sebagai 'benefit' (score/max), padahal harusnya criteria bertipe 'cost'
   dinormalisasi sebagai min/score.

YANG HARUS DIKERJAKAN:

1. Buat service class baru: app/Services/SawRankingService.php, dengan dua method public:
   calculateScore(int $applicationId): float dan recalculateRankingsForJob(int $jobId): void.
   Pindahkan SEMUA logic dari kedua controller ke service ini, lalu di kedua controller
   (Admin & HRD EvaluationController) tinggal panggil service ini — hapus method privat yang
   duplikat di kedua tempat.

2. Di dalam calculateScore(), perbaiki logic-nya:
   a. Ambil application_evaluations untuk application tersebut, GROUP BY criteria_id,
      hitung AVG(score) per criteria (rata-rata antar evaluator) — JANGAN treat tiap baris
      evaluator sebagai kontribusi terpisah.
   b. Untuk setiap criteria yang punya rata-rata skor, normalisasi sesuai `type`:
      - benefit: normalized = avg_score / max_value
      - cost: normalized = min_value / avg_score (jaga-jaga avg_score tidak 0, fallback ke
        normalized = 0 kalau avg_score <= 0)
   c. weightedScore = normalized * (weight / 100), akumulasikan ke totalScore.
   d. totalWeight = akumulasi (weight/100) HANYA untuk criteria yang benar-benar punya
      evaluasi (supaya application yang belum lengkap dinilai tidak otomatis dapat skor
      timpang). Return totalScore / totalWeight (bukan totalScore saja) — kalau totalWeight
      0, return 0.

3. Tambahkan info transparansi di halaman hrd/evaluations/show.blade.php dan
   admin/evaluations/show.blade.php: untuk setiap criteria, tampilkan skor dari MASING-MASING
   evaluator (nama evaluator + skor) dan nilai rata-ratanya — supaya HRD/admin bisa lihat
   ada berapa evaluator yang sudah menilai dan tidak menebak-nebak dari mana angka final-nya.

4. Tulis unit test sederhana (tests/Unit/SawRankingServiceTest.php) yang membuktikan:
   - 1 evaluator, semua criteria benefit -> hasil sesuai hitungan manual.
   - 2 evaluator menilai criteria yang sama dengan skor berbeda -> hasil = rata-rata, BUKAN
     dua kali lipat.
   - Ada criteria bertipe 'cost' -> arah normalisasinya kebalik dari benefit.

Setelah mengubah, jalankan ulang recalculateRankingsForJob untuk semua job yang punya
evaluasi (boleh lewat Artisan command/tinker), supaya data ranking lama yang sudah ada di
tabel application_rankings ikut diperbaiki, bukan cuma data baru ke depannya.
```

---

## 5. Template Surat Penerimaan/Penolakan Otomatis

```
Tambahkan fitur generate surat penerimaan/penolakan (PDF) otomatis di project Laravel
"rekruter" ini.

KONTEKS SAAT INI:
- Belum ada library PDF terpasang (cek composer.json, hanya laravel/framework dan laravel/tinker).
- Belum ada tabel/model untuk template surat.
- App\Http\Controllers\HRD\ApplicationController@update mengubah status application
  (pending/reviewed/accepted/rejected/interview_scheduled) tapi tidak melakukan apa pun selain
  update kolom status.
- App\Models\SystemSetting punya method statis getVal($key, $default) dan setVal($key, $value)
  untuk baca/tulis pengaturan, sudah dipakai untuk company_name dll — pakai pola yang sama.

YANG HARUS DIKERJAKAN:

1. Install dependency PDF: composer require barryvdh/laravel-dompdf

2. Migration baru: buat tabel `letter_templates`:
   - id, type (enum: 'acceptance','rejection','interview_invitation'), name (string),
     subject (string), body (longtext, isi HTML dengan placeholder seperti {{applicant_name}}),
     is_active (boolean default true), timestamps.

3. Model App\Models\LetterTemplate dengan $fillable yang sesuai.

4. Service app/Services/LetterGenerator.php:
   - Method generate(Application $application, string $type): string (return path file PDF
     yang disimpan).
   - Ambil LetterTemplate aktif sesuai $type, replace placeholder:
     {{applicant_name}} -> $application->applicant_name ?? $application->user->name,
     {{position}} -> $application->jobListing->position,
     {{company_name}} -> SystemSetting::getVal('company_name'),
     {{date}} -> tanggal hari ini format Indonesia.
   - Render ke PDF pakai Pdf::loadHTML(...)->save(...), simpan di
     storage/app/letters/{application_id}_{type}_{timestamp}.pdf, return path-nya.

5. CRUD template untuk Admin (BUKAN HRD — pengelolaan template surat termasuk konfigurasi
   sistem, jadi taruh di bawah middleware role:admin sesuai pola routes/web.php yang sudah ada):
   - Admin\LetterTemplateController (resource lengkap: index, create, store, edit, update, destroy)
   - Route::resource('letter-templates', Admin\LetterTemplateController::class) di group admin
   - Views admin/letter-templates/{index,create,edit}.blade.php — ikuti pola visual
     admin/criteria/index.blade.php (tabel + modal/form sederhana) yang sudah ada di project.
   - Seeder DatabaseSeeder: tambahkan 2 template default (acceptance & rejection) dengan body
     contoh wajar dalam Bahasa Indonesia.

6. Hook otomatis di HRD\ApplicationController@update: setelah status berhasil diubah jadi
   'accepted' atau 'rejected', panggil LetterGenerator->generate() dan simpan path filenya
   (tambahkan kolom baru `generated_letter_path` nullable di tabel applications via migration
   terpisah).

7. Tambahkan tombol "Unduh Surat" di hrd/applications/show.blade.php yang muncul kalau
   generated_letter_path tidak null, mengarah ke route baru
   GET /hrd/applications/{application}/letter -> stream PDF (gunakan response()->download()
   atau Storage::download()).

Pastikan generate surat tidak menggagalkan proses update status kalau template belum diisi
admin (bungkus pemanggilan LetterGenerator dengan try/catch + log, jangan biarkan exception
menggagalkan redirect sukses ke HRD).
```

---

## 6. Auto-Close Lowongan Berdasarkan Deadline/Kuota

```
Tambahkan fitur auto-close lowongan kerja berdasarkan deadline tanggal dan/atau kuota
pelamar di project Laravel "rekruter".

KONTEKS SAAT INI:
- Tabel job_listings (migration 2026_06_27_000001) belum punya kolom deadline/kuota, hanya
  status (enum: active/inactive/closed) yang harus diubah manual oleh HRD lewat
  HRD\JobController@update.
- Pelamaran publik masuk lewat JobController@apply (user login) dan @quickApply (tanpa login),
  keduanya bikin record baru di tabel `applications` tanpa pengecekan deadline/kuota sama sekali.
- Project pakai Laravel 13 (composer.json: laravel/framework ^13.8), artinya scheduling pakai
  routes/console.php dengan helper Schedule::command(), TIDAK pakai app/Console/Kernel.php
  (sudah dihapus di Laravel versi baru).

YANG HARUS DIKERJAKAN:

1. Migration baru: tambah ke tabel job_listings:
   - `application_deadline` (date, nullable)
   - `applicant_quota` (integer, nullable) — null artinya tanpa batas kuota.

2. Update HRD\JobController:
   - validateJob(): tambahkan validasi application_deadline => nullable|date|after_or_equal:today
     (saat create; saat edit boleh tanggal yang sudah lewat sedikit kalau memang sedang
     diedit ulang — cukup nullable|date saja untuk update), applicant_quota => nullable|integer|min:1.
   - Tambahkan dua input ini ke resources/views/hrd/jobs/_form.blade.php, ikuti style input
     lain yang sudah ada di form itu (label + text-input component).
   - Di index & detail lowongan, tampilkan sisa kuota (applicant_quota - applications_count)
     dan "Tutup otomatis pada: {deadline}" kalau diisi.

3. Guard di JobController publik (method apply() dan quickApply()):
   - Sebelum insert Application baru, cek dalam DB transaction + lockForUpdate() pada
     JobListing yang relevan:
     a. Jika job->status !== 'active' -> tolak dengan pesan "Lowongan ini sudah ditutup."
     b. Jika application_deadline tidak null dan sudah lewat -> tolak dengan pesan jelas,
        DAN set job->status = 'closed' saat itu juga.
     c. Jika applicant_quota tidak null dan job->applications()->count() >= applicant_quota
        -> tolak dengan pesan "Kuota pelamar untuk posisi ini sudah penuh.", DAN set
        job->status = 'closed'.
   - Pastikan pengecekan dan insert application terjadi dalam transaction yang sama supaya
     tidak race condition saat banyak orang submit bersamaan di kuota terakhir.

4. Artisan command baru: php artisan make:command CloseExpiredJobListings
   - Signature: jobs:close-expired
   - Logic: JobListing::where('status', 'active')
       ->whereNotNull('application_deadline')
       ->where('application_deadline', '<', now())
       ->update(['status' => 'closed']);
     (ini sebagai pengaman tambahan untuk lowongan yang sudah lewat deadline tapi tidak ada
     yang submit lamaran lagi setelahnya, jadi tidak ke-trigger oleh guard di poin 3)
   - Tambahkan log jumlah lowongan yang ditutup.

5. Daftarkan scheduler di routes/console.php:
   Schedule::command('jobs:close-expired')->daily();

6. Tampilkan info sisa kuota/deadline juga di halaman publik resources/views/jobs/show.blade.php
   supaya calon pelamar tahu batas waktu/kuota sebelum melamar (jangan tampilkan kalau
   application_deadline/applicant_quota null).

Jangan ubah enum status di job_listings (tetap active/inactive/closed), 'closed' otomatis
dan 'closed' manual pakai value yang sama — cukup pastikan tidak ada efek samping kalau HRD
ingin re-open lowongan yang auto-closed (set ulang ke active + boleh update deadline/kuota
lewat form edit yang sudah ada).
```

---

### Cara pakai
1. Mulai dari **#4 (perbaikan bug SAW)** dan **#3 (filter & pagination)** — keduanya memengaruhi data yang sudah berjalan dan paling berisiko kalau dibiarkan lama.
2. Lalu **#2 (bulk action)** dan **#6 (auto-close)** — peningkatan operasional harian HRD.
3. Terakhir **#1 (self-schedule)** dan **#5 (template surat)** — fitur baru yang lebih besar scope-nya, lebih baik dikerjakan setelah fondasi di atas stabil.

Tiap prompt didesain berdiri sendiri, jadi aman dijalankan satu-satu di sesi/branch terpisah dan di-review lewat PR masing-masing sebelum lanjut ke yang berikutnya.
