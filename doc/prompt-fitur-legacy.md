# Prompt Implementasi — Fitur dari Sistem Legacy

Saya cek lebih dalam ke folder `legacy/` sebelum menulis prompt ini, dan ada **3 koreksi** dari ringkasan awal saya supaya prompt-nya akurat:

- **CV pelamar**: ternyata SUDAH ada link-nya di `admin/applications/index.blade.php` dan `hrd/applications/show.blade.php` (`asset('storage/'.$app->resume_path)`), tapi link itu **publik tanpa otentikasi** — siapa pun yang punya/menebak URL-nya bisa lihat CV orang lain tanpa login. Legacy justru lebih aman karena `download_cv.php` mengecek role admin/hrd dulu. Jadi ini sebenarnya **isu keamanan/privasi**, bukan cuma fitur yang "belum ada".
- **"Login Google OAuth"**: setelah saya cek `google_callback.php` dan `CalendarIntegration.php`, ini BUKAN fitur "Sign in with Google" untuk login pengguna — ini adalah **OAuth untuk sinkronisasi Google Calendar** milik interviewer (supaya jadwal interview otomatis masuk ke Google Calendar mereka). Tidak ada satu pun referensi "login with Google" di `legacy/auth/`. Saya tulis ulang prompt-nya sesuai ini.
- **Video interview**: legacy TIDAK punya video call sungguhan (bukan WebRTC). Yang ada cuma: generate link meeting otomatis berformat `https://meet.google.com/interview_<id acak>`, tombol "Join Meeting" yang sekadar `window.open()` ke link itu, dan panel referensi daftar pertanyaan interview di sampingnya. Tombol "Share Screen"/"Mute" di legacy juga cuma `alert('akan diimplementasikan dengan WebRTC API')` — placeholder yang tidak pernah benar-benar dibangun. Saya buat prompt sesuai skala asli ini, bukan video call sungguhan.

---

## 1. Perbaikan Akses CV Pelamar (Admin & HRD)

```
Perbaiki cara admin dan HRD mengakses CV/resume pelamar di project Laravel "rekruter" ini.
Ini perbaikan KEAMANAN, bukan cuma penambahan fitur baru.

KONTEKS SAAT INI (sudah saya cek langsung ke kode):
- Saat melamar, file resume disimpan lewat $request->file('resume')->store('resumes', 'public')
  di app/Http/Controllers/JobController.php (method apply() dan quickApply()). Artinya file
  fisik ada di storage/app/public/resumes/ dan otomatis ikut ter-expose lewat symlink
  public/storage karena disimpan di disk 'public'.
- resources/views/admin/applications/index.blade.php dan resources/views/hrd/applications/show.blade.php
  menampilkan link CV lewat asset('storage/' . $app->resume_path) yang dibuka target="_blank".
  Link ini BISA DIAKSES SIAPA SAJA tanpa login selama tahu/menebak URL-nya, karena disk 'public'
  disajikan langsung oleh web server, tidak lewat middleware auth Laravel sama sekali.
- App\Http\Controllers\Admin\ApplicationController saat ini HANYA punya method index(), dan
  TIDAK ADA route untuk itu di routes/web.php sama sekali (grep routes/web.php untuk
  "Admin\\ApplicationController" hasilnya nihil) — jadi halaman daftar lamaran admin ini
  sebenarnya tidak bisa diakses lewat URL apa pun saat ini.
- App\Http\Controllers\HRD\ApplicationController sudah punya index()/show()/update() dan SUDAH
  diroutekan dengan benar di group role:hrd.

YANG HARUS DIKERJAKAN:

1. Migration data: pindahkan resume yang sudah ada dari disk 'public' ke disk 'local'
   (private, tidak ter-expose lewat /storage). Buat Artisan command sekali pakai
   php artisan make:command MoveResumesToPrivateDisk yang:
   - Loop semua Application yang resume_path tidak null.
   - Copy file dari Storage::disk('public') ke Storage::disk('local') dengan path yang sama.
   - Update kolom resume_path kalau perlu (boleh tetap sama, asal disk-nya beda).
   - Hapus file lama dari disk 'public' setelah berhasil dipindah & diverifikasi ada di disk baru.

2. Ubah JobController@apply dan @quickApply: ganti
   $request->file('resume')->store('resumes', 'public')
   menjadi
   $request->file('resume')->store('resumes', 'local')
   supaya upload baru otomatis privat sejak awal.

3. Tambahkan method downloadResume(Application $application) di HRD\ApplicationController:
   - Validasi resume_path tidak null dan file benar-benar ada di Storage::disk('local'),
     kalau tidak ada return 404 dengan pesan jelas (jangan biarkan exception mentah).
   - Return Storage::disk('local')->download($application->resume_path,
     'CV_' . Str::slug($application->applicant_name ?? $application->user->name) . '.pdf')
     (sesuaikan ekstensi asli file, jangan dihardcode .pdf kalau aslinya .docx).
   - Route: GET /hrd/applications/{application}/resume -> name hrd.applications.resume,
     daftarkan di group role:hrd yang sudah ada di routes/web.php.

4. Untuk Admin: dulu route-nya memang belum pernah didaftarkan. Tambahkan supaya konsisten:
   - Route::resource('applications', Admin\ApplicationController::class)->only(['index','show'])
     di group role:admin pada routes/web.php (di bawah baris questions resource yang sudah ada).
   - Tambahkan method show(Application $application) di Admin\ApplicationController (sekarang
     belum ada), load relasi user/jobListing/evaluations seperti pola di HRD\ApplicationController@show.
   - Tambahkan method downloadResume() yang identik dengan milik HRD (boleh ekstrak ke trait
     App\Http\Controllers\Concerns\DownloadsResume kalau mau hindari duplikasi, dipakai oleh
     kedua controller).
   - Route: GET /admin/applications/{application}/resume -> name admin.applications.resume.

5. Ganti SEMUA pemakaian asset('storage/' . $app->resume_path) di
   resources/views/admin/applications/index.blade.php dan resources/views/hrd/applications/show.blade.php
   menjadi link ke route baru (route('hrd.applications.resume', $app) atau
   route('admin.applications.resume', $app) sesuai konteksnya), HAPUS target="_blank" yang
   membuka langsung ke /storage/... karena file tidak lagi ada di sana.

Setelah semua jalan, jalankan php artisan storage:link tidak relevan lagi untuk folder resumes
(boleh tetap ada untuk file publik lain seperti logo perusahaan), tapi pastikan tidak ada lagi
referensi 'resumes' dengan disk 'public' di kode manapun — grep ulang sebelum selesai.
```

---

## 2. Laporan & Analitik (Dashboard Reports)

```
Tambahkan halaman Laporan terpisah (bukan di dashboard utama) untuk Admin dan HRD di project
Laravel "rekruter" ini, meniru legacy/admin/reports.php dan legacy/hrd/reports.php tapi
dengan query yang lebih baik (lihat catatan bug di bawah).

KONTEKS SAAT INI:
- Admin\DashboardController dan HRD\DashboardController hanya menampilkan angka mentah
  (userCount, criteriaCount, questionCount untuk admin; applicationCount, interviewCount,
  activeJobCount untuk HRD) tanpa grafik/breakdown sama sekali.
- Project belum memuat Chart.js di mana pun (cek resources/views/layouts/app.blade.php,
  cuma ada FontAwesome via CDN + Vite untuk app.css/app.js). package.json juga tidak punya
  chart.js sebagai dependency.
- BUG di legacy yang JANGAN dibawa ke versi baru: query bulanan legacy
  (GROUP BY DATE_FORMAT(applied_at,'%Y-%m')) melewatkan bulan yang nol aplikasi, jadi chart
  garis di legacy bisa terlihat "melompat" karena bulan kosong hilang dari sumbu X, bukan
  benar-benar bernilai 0. Versi baru harus menampilkan SEMUA bulan dalam rentang waktu
  (termasuk yang nilainya 0) supaya grafik akurat.
- Tombol "Export PDF"/"Export Excel" di legacy/admin/reports.php HANYA alert('Fitur export
  PDF akan segera tersedia') — tidak pernah benar-benar berfungsi. JANGAN ikut menyalin tombol
  placeholder itu; cukup sediakan tombol "Print" (window.print(), ini satu-satunya yang benar2
  berfungsi di legacy) untuk sekarang.

YANG HARUS DIKERJAKAN:

1. npm install chart.js, lalu import di resources/js/app.js (import Chart from 'chart.js/auto'
   atau registrasi komponen yang dipakai) supaya ikut di-bundle Vite, bukan load via CDN
   (lebih konsisten dengan setup project yang sudah pakai Vite untuk asset lain).

2. Buat route & controller baru (PISAH dari DashboardController, ikut pola legacy yang
   memisahkan dashboard.php dari reports.php):
   - HRD\ReportController@index -> route hrd.reports.index, daftarkan di group role:hrd.
   - Admin\ReportController@index -> route admin.reports.index, daftarkan di group role:admin.

3. HRD\ReportController@index harus menyiapkan data (meniru hrd/reports.php legacy):
   - Tren aplikasi 6 bulan terakhir (array bulan + jumlah, isi 0 untuk bulan tanpa data —
     gunakan Carbon::now()->subMonths(5)->startOfMonth() lalu loop per bulan, JANGAN cuma
     groupBy hasil query mentah).
   - Distribusi status (pending/reviewed/accepted/rejected/interview_scheduled) — hitung
     pakai Application::where('...')->groupBy('status')->count() atau selectRaw count per status.
   - Top 5 lowongan paling banyak dilamar (join job_listings + applications, orderByDesc count).
   - 10 aplikasi terbaru (Application::with(['user','jobListing'])->latest()->limit(10)->get()).
   - 4 angka ringkasan: total aplikasi, menunggu review, diterima, lowongan aktif.

4. Admin\ReportController@index harus menyiapkan data (meniru admin/reports.php legacy):
   - Tren aplikasi 12 bulan terakhir (isi 0 untuk bulan tanpa data, sama seperti poin 3).
   - Aplikasi per lowongan (semua lowongan, bukan cuma top 5, untuk bar chart).
   - Distribusi status (4 kategori, sama seperti HRD).
   - 4 angka ringkasan: total lowongan, lowongan aktif, total aplikasi, pelamar terdaftar.

5. View hrd/reports/index.blade.php dan admin/reports/index.blade.php:
   - Card ringkasan angka (ikuti style card yang sudah dipakai di hrd/dashboard.blade.php
     dan admin/dashboard.blade.php — jangan bikin style baru).
   - Chart garis untuk tren bulanan (Chart.js type 'line').
   - Chart doughnut untuk distribusi status (HRD) / chart bar untuk aplikasi per lowongan (Admin).
   - List/tabel detail (top lowongan, aplikasi terbaru) di bawah chart.
   - Satu tombol "Print Laporan" (window.print()) saja — jangan tambahkan tombol export yang
     tidak benar-benar berfungsi.

6. Tambahkan link menu "Laporan" di resources/views/layouts/sidebar.php untuk kedua role,
   ikuti pola item menu yang sudah ada (ikon FontAwesome fas fa-chart-bar seperti di legacy).

Catatan: kalau nanti memang dibutuhkan export PDF/Excel yang sungguhan (bukan placeholder),
itu di luar scope prompt ini — perlu task terpisah pakai package seperti barryvdh/laravel-dompdf
atau maatwebsite/excel.
```

---

## 3. Auto-Generate Link Meeting Saat Interview Video Dikonfirmasi

```
Tambahkan fitur auto-generate link meeting untuk interview tipe video di project Laravel
"rekruter" ini. PENTING: ini BUKAN video call sungguhan (tidak ada WebRTC), persis seperti
legacy — cukup generator link + panel referensi pertanyaan, jangan over-engineer.

KONTEKS SAAT INI (sudah saya cek langsung):
- legacy/includes/InterviewManager.php method generateMeetingLink($interviewType) isinya
  cuma: kalau tipe 'video', return "https://meet.google.com/interview_" . uniqid() — sekadar
  string URL berformat Google Meet, BUKAN benar-benar membuat meeting via Google Meet API.
- legacy/admin/video_interview.php dan legacy/hrd/video_interview.php menampilkan link itu di
  text input read-only + tombol "Join Meeting" yang isinya cuma window.open(meetingLink).
  Tombol "Share Screen"/"Mute" di sana cuma alert('akan diimplementasikan dengan WebRTC API')
  — tidak pernah dibangun, JANGAN ikut dibuat di versi baru (itu fitur kosong).
- Di samping link meeting, legacy menampilkan panel pertanyaan interview yang dikelompokkan
  per question_type (general/technical/behavioral/situational) dari tabel interview_questions,
  supaya interviewer punya referensi cepat saat menelepon/video call kandidat.
- App\Http\Controllers\HRD\InterviewController dan Admin\InterviewController di versi baru
  SAAT INI HANYA punya method index() — belum ada create/store untuk benar-benar menjadwalkan
  interview baru sama sekali. Tabel interview_schedules sudah lengkap kolomnya (interview_type,
  interview_date, meeting_link, status, dst — lihat migration 2026_06_27_000007).

YANG HARUS DIKERJAKAN:

1. HRD\InterviewController: tambahkan method create(), store(Request $request), show(InterviewSchedule $interview).
   - Form sederhana: pilih application_id (dari aplikasi yang status-nya pending/reviewed/
     interview_scheduled), interviewer_id (dari User dengan role hrd, atau role lain yang
     ditugaskan jadi interviewer), interview_type (phone/video/in-person/online),
     interview_date, duration_minutes, notes.
   - Di store(): kalau interview_type 'video' atau 'online', generate meeting_link otomatis
     dengan format yang sama gaya legacy tapi pakai helper Laravel:
     'https://meet.google.com/interview-' . \Illuminate\Support\Str::random(10)
     (acak yang lebih aman dari uniqid() PHP biasa). Kalau interview_type 'phone' atau
     'in-person', meeting_link dikosongkan dan meeting_room boleh diisi manual oleh HRD.
   - Setelah simpan, update applications.interview_status jadi 'scheduled' dan
     applications.status jadi 'interview_scheduled'.
   - Tambahkan juga method regenerateLink(InterviewSchedule $interview) untuk HRD generate
     ulang link baru kalau yang lama dirasa bermasalah/expired (cukup overwrite meeting_link
     dengan format yang sama).

2. Halaman detail interview (hrd/interviews/show.blade.php — baru, sebelumnya cuma ada index):
   - Tampilkan info interview (pelamar, posisi, tanggal & waktu, timezone) — sama seperti
     section "Informasi Interview" di legacy.
   - Untuk interview_type video/online: tampilkan meeting_link di text input readonly +
     tombol copy-to-clipboard (pakai navigator.clipboard.writeText, BUKAN document.execCommand
     yang dipakai legacy karena sudah deprecated) + tombol "Buka Meeting" yang
     window.open(meetingLink, '_blank'). JANGAN buat kotak video placeholder atau tombol
     share screen/mute — itu fitur kosong di legacy yang tidak pernah berfungsi.
   - Panel referensi pertanyaan: tampilkan InterviewQuestion::where('is_active', true)
     ->where(fn($q) => $q->whereNull('job_position')->orWhere('job_position', $interview->application->jobListing->position))
     ->get()->groupBy('question_type'), tampilkan per kategori dalam accordion/tab sederhana
     (general/technical/behavioral/situational/cultural) supaya interviewer bisa lihat cepat
     saat sedang interview.
   - Tombol "Isi Feedback" yang mengarah ke halaman feedback (kalau belum ada controller untuk
     ini, itu di luar scope prompt ini — cukup buat link-nya dulu mengarah ke route placeholder
     hrd.interviews.feedback yang akan dikerjakan di task terpisah).

3. Duplikasi struktur yang sama untuk Admin\InterviewController dan view admin/interviews/,
   dengan route yang juga belum pernah didaftarkan di routes/web.php — tambahkan
   Route::resource('interviews', Admin\InterviewController::class)->only(['index','create','store','show'])
   di group role:admin.

Jangan implementasikan video call embed/WebRTC sungguhan — itu di luar scope dan tidak ada
di legacy juga (legacy cuma window.open ke link eksternal). Kalau HRD mau pakai Zoom/Google
Meet asli, mereka generate link itu sendiri secara manual lalu bisa override meeting_link
lewat form edit interview.
```

---

## 4. Sinkronisasi Google Calendar untuk Interviewer

```
Tambahkan integrasi Google Calendar di project Laravel "rekruter" ini, supaya interview yang
dijadwalkan otomatis muncul di Google Calendar milik interviewer. CATATAN: fitur ini BUKAN
"Login with Google" untuk autentikasi pengguna — ini OAuth terpisah khusus untuk akses
Calendar API milik interviewer yang sudah login dengan email/password biasa.

KONTEKS SAAT INI (sudah saya cek langsung):
- legacy/includes/CalendarIntegration.php punya $googleClientId = 'YOUR_GOOGLE_CLIENT_ID' dan
  $googleClientSecret = 'YOUR_GOOGLE_CLIENT_SECRET' — masih PLACEHOLDER, kredensial Google
  Cloud Console belum pernah benar-benar dikonfigurasi di legacy. Jadi fitur ini di legacy pun
  belum pernah jalan end-to-end di production, hanya scaffolding.
- legacy/auth/google_callback.php menerima ?code= dan ?state= dari Google, lalu panggil
  CalendarIntegration::handleGoogleCallback(), redirect balik ke admin/interviews.php dengan
  query calendar_success=1 atau calendar_error=1.
- Scope OAuth yang diminta: 'https://www.googleapis.com/auth/calendar' (akses penuh ke
  Calendar API, BUKAN scope login/profile).
- Method yang ada di legacy: getGoogleAuthUrl($userId), handleGoogleCallback($code, $state),
  exchangeCodeForTokens($code), createGoogleCalendarEvent($userId, $interviewData),
  updateGoogleCalendarEvent(), deleteGoogleCalendarEvent(), refreshGoogleToken().
- Tabel calendar_integrations di Laravel sudah PAS dengan kebutuhan ini (user_id,
  integration_type enum google/outlook/apple, access_token, refresh_token, calendar_id,
  is_active, expires_at) — TIDAK PERLU migration baru, model App\Models\CalendarIntegration
  juga sudah ada, cuma belum dipakai controller manapun.
- Tabel interview_schedules sudah punya kolom google_calendar_event_id yang juga belum
  pernah diisi oleh kode manapun.

YANG HARUS DIKERJAKAN:

1. composer require google/apiclient:^2.15 — pakai SDK resmi Google daripada hand-roll HTTP
   call ke OAuth/Calendar API, supaya penanganan refresh token dan error lebih reliable.

2. Tambahkan ke .env.example (dan .env lokal): GOOGLE_CALENDAR_CLIENT_ID,
   GOOGLE_CALENDAR_CLIENT_SECRET, GOOGLE_CALENDAR_REDIRECT_URI. Tambahkan juga ke
   config/services.php di bawah key 'google_calendar' => [...] (pakai env() di sana, jangan
   hardcode kredensial di kode seperti legacy).

3. Buat app/Services/GoogleCalendarService.php:
   - getAuthUrl(int $userId): string — generate URL consent Google, simpan $userId di
     parameter 'state' (encrypt dulu pakai Crypt::encryptString supaya tidak bisa dipalsukan).
   - handleCallback(string $code, string $state): bool — decrypt state untuk dapat user_id,
     tukar $code jadi access_token+refresh_token, simpan/update ke CalendarIntegration
     (updateOrCreate berdasarkan user_id + integration_type='google').
   - createEvent(InterviewSchedule $interview): ?string — return Google event ID atau null
     kalau interviewer belum connect/integration tidak aktif. Isi event: judul "Interview:
     {nama pelamar} - {posisi}", waktu dari interview_date+duration_minutes, deskripsi dari
     notes, tambahkan meeting_link sebagai location/description kalau ada.
   - updateEvent(InterviewSchedule $interview): void — dipanggil saat interview di-reschedule.
   - deleteEvent(InterviewSchedule $interview): void — dipanggil saat interview dibatalkan.
   - refreshTokenIfExpired(CalendarIntegration $integration): CalendarIntegration — cek
     expires_at, kalau sudah lewat, pakai refresh_token untuk dapat access_token baru.

4. Route baru (cukup di group middleware 'auth' biasa, BUKAN di bawah role:hrd/admin khusus,
   karena siapa pun yang punya jadwal interview bisa connect kalender pribadinya):
   - GET /calendar/connect/google -> CalendarIntegrationController@redirectToGoogle
   - GET /calendar/google/callback -> CalendarIntegrationController@handleGoogleCallback
     (route ini TIDAK butuh middleware auth karena Google akan redirect browser user balik
     ke sini setelah consent — pastikan tetap validasi 'state' terenkripsi untuk keamanan,
     bukan asal percaya parameter dari luar).
   - POST /calendar/disconnect -> CalendarIntegrationController@disconnect (set is_active=false).

5. UI: tambahkan section kecil "Integrasi Kalender" di resources/views/profile/edit.blade.php
   (halaman profil yang sudah ada), menampilkan status "Terhubung dengan Google Calendar" /
   tombol "Hubungkan Google Calendar" sesuai status CalendarIntegration milik user yang login.

6. Hook otomatis: di HRD\InterviewController@store (dan @update kalau ada reschedule, serta
   method cancel kalau dibuat), panggil GoogleCalendarService sesuai event-nya (create/update/
   delete), simpan hasil event ID ke kolom google_calendar_event_id yang sudah ada di
   interview_schedules. Bungkus semua panggilan API Google dengan try/catch + Log::warning(),
   JANGAN biarkan kegagalan Google Calendar API menggagalkan penjadwalan interview itu sendiri
   (kalender cuma fitur tambahan, bukan dependency wajib).

CATATAN PENTING: fitur ini tidak akan benar-benar berfungsi sampai tim membuat OAuth Client
ID asli di Google Cloud Console (https://console.cloud.google.com/) dengan Calendar API
diaktifkan, dan redirect URI didaftarkan sesuai domain production. Sebelum itu, kerjakan dulu
sampai tahap "siap pakai begitu kredensial diisi", boleh ditest pakai dummy/sandbox credential
dulu untuk pastikan flow OAuth-nya benar.
```

---

## 5. Preview Detail Lamaran & Evaluasi via Modal (Tanpa Reload Halaman)

```
Tambahkan modal preview cepat untuk detail lamaran dan detail evaluasi di halaman daftar HRD
(dan siapkan juga untuk Admin) pada project Laravel "rekruter" ini, supaya HRD bisa lihat
detail tanpa pindah halaman dulu — meniru UX legacy/admin/get_application_detail.php dan
legacy/admin/get_evaluation_detail.php yang dipanggil via fetch() dari applications.php.

KONTEKS SAAT INI:
- HRD\ApplicationController@show dan HRD\EvaluationController@show SUDAH ADA dan sudah
  diroutekan dengan benar — tapi keduanya full page reload (pindah ke halaman baru), bukan
  modal. Ini sebenarnya tidak rusak, cuma UX-nya lebih lambat dibanding legacy untuk HRD yang
  perlu cek banyak lamaran berurutan dari satu tabel.
- Admin\ApplicationController dan Admin\EvaluationController malah belum punya method show()
  sama sekali, dan belum diroutekan (lihat juga prompt "Perbaikan Akses CV Pelamar" di file
  ini, poin 4 — kerjakan itu DULU sebagai prasyarat sebelum prompt ini, supaya
  admin.applications.show sudah ada).
- Project sudah pakai Alpine.js (lihat resources/views/components/dropdown.blade.php dan
  components/modal.blade.php yang sudah ada) — pakai itu untuk modal, JANGAN install library
  modal/JS tambahan.

YANG HARUS DIKERJAKAN:

1. Endpoint JSON baru (bukan ganti show() yang sudah ada — show() full page TETAP dipertahankan
   sebagai fallback/link langsung, modal ini cuma jalur cepat tambahan dari tabel):
   - GET /hrd/applications/{application}/preview -> HRD\ApplicationController@preview,
     return response()->json() berisi data application + jobListing + evaluations.criteria
     (sudah di-load relasinya), TANPA password/token sensitif apa pun.
   - GET /hrd/evaluations/{application}/preview -> HRD\EvaluationController@preview,
     return JSON berisi evaluations per criteria + hasil hitungan skor SAW (pakai service
     SawRankingService kalau prompt "Multi-Evaluator Aggregation" dari sesi sebelumnya sudah
     dikerjakan; kalau belum, panggil method private yang ada sekarang apa adanya).
   - Duplikasi pola yang sama untuk Admin\ApplicationController dan Admin\EvaluationController.

2. Di resources/views/hrd/applications/index.blade.php dan hrd/evaluations/index.blade.php
   (juga versi admin-nya): ubah baris tabel jadi punya dua aksi:
   - Link "Lihat Detail Lengkap" -> tetap ke route show() yang sudah ada (full page).
   - Tombol "Preview Cepat" (ikon mata) yang pakai Alpine x-data untuk fetch() ke endpoint
     /preview di atas, tampilkan hasilnya di components/modal.blade.php yang sudah ada di
     project (jangan bikin komponen modal baru), isi modal dengan skeleton loading dulu
     sebelum data datang.
   - Modal preview minimal menampilkan: nama & kontak pelamar, posisi yang dilamar, status
     saat ini, ringkasan skor evaluasi (kalau sudah ada), dan tombol "Lihat Detail Lengkap"
     di dalam modal yang mengarah ke halaman show() penuh untuk yang butuh aksi lanjutan
     (ubah status, isi evaluasi baru, dst — JANGAN taruh form aksi di dalam modal preview ini,
     biar scope-nya tetap ringan sebagai "lihat cepat" saja).

3. Tangani error dengan baik di sisi Alpine: kalau fetch gagal (network error / 403 / 404),
   tampilkan pesan error di dalam modal, JANGAN biarkan modal kosong tanpa penjelasan.

Jangan hapus halaman show() penuh yang sudah ada — modal ini pelengkap untuk mempercepat
review banyak lamaran dari tabel, bukan pengganti halaman detail.
```

---

### Catatan urutan pengerjaan
1. **#1 (akses CV)** paling dulu — ini perbaikan keamanan/privasi data pelamar, bukan sekadar fitur baru.
2. **#5 (preview modal)** baru bisa jalan penuh untuk Admin setelah bagian routing di **#1 poin 4** dikerjakan (Admin belum punya route `show` untuk applications/evaluations sama sekali).
3. **#3 (auto-link meeting)** dan **#2 (laporan)** bisa dikerjakan independen, kapan saja.
4. **#4 (Google Calendar)** paling baik dikerjakan terakhir karena butuh setup eksternal (Google Cloud Console) di luar kode, dan baru benar-benar berguna kalau **#3** (alur penjadwalan interview) sudah ada.
