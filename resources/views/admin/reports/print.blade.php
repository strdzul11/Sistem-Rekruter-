<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analitik Sistem Rekruter - {{ $generatedAt }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            font-size: 10pt;
            color: #1e293b;
            background: #f1f5f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ===== PAGE SETUP ===== */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 4px 30px rgba(0,0,0,0.12);
            border-radius: 4px;
            overflow: hidden;
        }

        @media print {
            body { background: #fff; }
            .page { margin: 0; box-shadow: none; border-radius: 0; width: 100%; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 0; }
        }

        /* ===== KOP SURAT ===== */
        .header-banner {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 60%, #3b82f6 100%);
            padding: 28px 36px 24px;
            color: white;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
        }
        .company-info h1 {
            font-size: 20pt;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .company-info p {
            font-size: 9pt;
            opacity: 0.8;
            margin-top: 3px;
            font-weight: 400;
        }
        .report-badge {
            text-align: right;
        }
        .report-badge .badge-title {
            font-size: 8pt;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0.75;
        }
        .report-badge .badge-date {
            font-size: 9pt;
            font-weight: 500;
            margin-top: 4px;
            opacity: 0.9;
        }
        .header-divider {
            height: 1px;
            background: rgba(255,255,255,0.2);
            margin-bottom: 16px;
        }
        .header-subtitle {
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header-subtitle span {
            font-weight: 400;
            opacity: 0.8;
            font-size: 10pt;
            margin-left: 8px;
        }

        /* ===== CONTENT ===== */
        .content {
            padding: 28px 36px;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::before {
            content: '';
            display: inline-block;
            width: 3px;
            height: 14px;
            background: #2563eb;
            border-radius: 2px;
        }
        .section { margin-bottom: 24px; }

        /* ===== STAT CARDS ===== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }
        .stat-card {
            padding: 14px 16px;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .stat-card.blue  { background: #eff6ff; border-color: #2563eb; }
        .stat-card.green { background: #f0fdf4; border-color: #16a34a; }
        .stat-card.purple{ background: #faf5ff; border-color: #9333ea; }
        .stat-card.amber { background: #fffbeb; border-color: #d97706; }
        .stat-card.rose  { background: #fff1f2; border-color: #e11d48; }
        .stat-card.teal  { background: #f0fdfa; border-color: #0d9488; }
        .stat-card .label {
            font-size: 7.5pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .stat-card .value {
            font-size: 20pt;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }
        .stat-card .sub {
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* ===== DUAL GRID ===== */
        .dual-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        /* ===== STATUS BADGES ===== */
        .status-table { width: 100%; border-collapse: collapse; }
        .status-table tr td {
            padding: 7px 10px;
            vertical-align: middle;
            font-size: 9pt;
        }
        .status-table tr:not(:last-child) td {
            border-bottom: 1px solid #f1f5f9;
        }
        .status-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            margin-right: 7px;
        }
        .status-bar-wrap {
            background: #f1f5f9;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }
        .status-bar { height: 100%; border-radius: 4px; }

        /* ===== MAIN TABLE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .data-table thead tr {
            background: #1e3a5f;
            color: white;
        }
        .data-table thead th {
            padding: 9px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }
        .data-table thead th:last-child { text-align: right; }
        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .data-table tbody tr:hover { background: #eff6ff; }
        .data-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .data-table tbody td:last-child { text-align: right; }
        .data-table tfoot td {
            padding: 9px 12px;
            font-weight: 700;
            font-size: 9pt;
            background: #f8fafc;
            border-top: 2px solid #e2e8f0;
        }
        .data-table tfoot td:last-child { text-align: right; color: #2563eb; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-active   { background: #dcfce7; color: #166534; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }
        .badge-pending  { background: #fef9c3; color: #854d0e; }
        .badge-reviewed { background: #dbeafe; color: #1e40af; }
        .badge-interview{ background: #ede9fe; color: #5b21b6; }
        .badge-accepted { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* ===== TREND TABLE ===== */
        .trend-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
        }
        .trend-cell {
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
        }
        .trend-cell .t-month {
            font-size: 7pt;
            color: #94a3b8;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .trend-cell .t-val {
            font-size: 14pt;
            font-weight: 800;
            color: #1e3a5f;
        }
        .trend-cell .t-bar {
            height: 4px;
            background: #2563eb;
            border-radius: 2px;
            margin-top: 5px;
        }

        /* ===== TOP JOBS ===== */
        .top-job-row {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 10px;
        }
        .top-job-rank {
            width: 22px; height: 22px;
            border-radius: 50%;
            background: #1e3a5f;
            color: white;
            font-weight: 800;
            font-size: 8pt;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .top-job-rank.gold   { background: #d97706; }
        .top-job-rank.silver { background: #64748b; }
        .top-job-rank.bronze { background: #92400e; }
        .top-job-name { flex: 1; font-size: 9pt; font-weight: 600; color: #0f172a; }
        .top-job-company { font-size: 7.5pt; color: #94a3b8; font-weight: 400; }
        .top-job-count {
            font-size: 11pt;
            font-weight: 800;
            color: #2563eb;
        }
        .top-job-sub { font-size: 7pt; color: #94a3b8; text-align: right; }

        /* ===== FOOTER ===== */
        .print-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 8pt;
            color: #94a3b8;
        }
        .footer-left .fl-title { font-weight: 700; font-size: 9pt; color: #334155; }
        .footer-right { text-align: right; }
        .signature-box {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .sig-item { text-align: center; }
        .sig-item .sig-label { font-size: 8pt; font-weight: 600; color: #475569; }
        .sig-item .sig-line {
            margin: 40px auto 4px;
            width: 80%;
            height: 1px;
            background: #cbd5e1;
        }
        .sig-item .sig-name { font-size: 8pt; color: #475569; }

        /* ===== PRINT BTN ===== */
        .print-btn {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 14px 28px;
            font-size: 10pt;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(37,99,235,0.35);
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .print-btn:hover { background: #1d4ed8; transform: translateY(-2px); }
        .back-btn {
            position: fixed;
            bottom: 28px;
            left: 28px;
            background: white;
            color: #475569;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 10pt;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }
        .back-btn:hover { border-color: #94a3b8; }
    </style>
</head>
<body>

{{-- Tombol aksi (hanya tampil di layar) --}}
<a href="{{ route('admin.reports.index') }}" class="back-btn no-print">
    &#8592; Kembali
</a>
<button onclick="window.print()" class="print-btn no-print">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8" rx="1"/>
    </svg>
    Cetak / Simpan PDF
</button>

<div class="page">

    {{-- ===== KOP SURAT ===== --}}
    <div class="header-banner">
        <div class="header-top">
            <div class="company-info">
                <h1>{{ config('app.name', 'Sistem Rekruter') }}</h1>
                <p>Sistem Informasi Manajemen Rekrutmen &amp; Seleksi Karyawan</p>
            </div>
            <div class="report-badge">
                <div class="badge-title">Laporan Resmi</div>
                <div class="badge-date">{{ $generatedAt }} WIB</div>
            </div>
        </div>
        <div class="header-divider"></div>
        <div class="header-subtitle">
            Laporan Analitik &amp; Statistik Sistem
            <span>— Data rekrutmen terintegrasi</span>
        </div>
    </div>

    <div class="content">

        {{-- ===== RINGKASAN STATISTIK UTAMA ===== --}}
        <div class="section">
            <div class="section-title">Ringkasan Statistik Utama</div>
            <div class="stat-grid">
                <div class="stat-card blue">
                    <div class="label">Total Lowongan</div>
                    <div class="value">{{ $summary['total_jobs'] }}</div>
                    <div class="sub">{{ $summary['active_jobs'] }} aktif &bull; {{ $summary['inactive_jobs'] }} nonaktif</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Total Lamaran</div>
                    <div class="value">{{ $summary['total_applications'] }}</div>
                    <div class="sub">Seluruh periode</div>
                </div>
                <div class="stat-card purple">
                    <div class="label">Pelamar Terdaftar</div>
                    <div class="value">{{ $summary['registered_users'] }}</div>
                    <div class="sub">Akun pelamar aktif</div>
                </div>
                <div class="stat-card amber">
                    <div class="label">Tim HRD</div>
                    <div class="value">{{ $summary['hrd_users'] }}</div>
                    <div class="sub">Pengguna HRD</div>
                </div>
            </div>
            <div class="stat-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:0;">
                <div class="stat-card amber">
                    <div class="label">Menunggu Review</div>
                    <div class="value" style="font-size:16pt;">{{ $summary['pending_apps'] }}</div>
                    <div class="sub">Lamaran pending</div>
                </div>
                <div class="stat-card green">
                    <div class="label">Lamaran Diterima</div>
                    <div class="value" style="font-size:16pt;">{{ $summary['accepted_apps'] }}</div>
                    <div class="sub">Status accepted</div>
                </div>
                <div class="stat-card rose">
                    <div class="label">Lamaran Ditolak</div>
                    <div class="value" style="font-size:16pt;">{{ $summary['rejected_apps'] }}</div>
                    <div class="sub">Status rejected</div>
                </div>
            </div>
        </div>

        {{-- ===== STATUS & TOP JOBS ===== --}}
        <div class="dual-grid">
            {{-- Distribusi Status --}}
            <div class="section" style="margin-bottom:0;">
                <div class="section-title">Distribusi Status Lamaran</div>
                @php
                    $totalApps = $summary['total_applications'] ?: 1;
                    $statusColors = [
                        'pending'             => ['dot'=>'#d97706','bar'=>'#fbbf24'],
                        'reviewed'            => ['dot'=>'#2563eb','bar'=>'#60a5fa'],
                        'interview_scheduled' => ['dot'=>'#9333ea','bar'=>'#c084fc'],
                        'accepted'            => ['dot'=>'#16a34a','bar'=>'#4ade80'],
                        'rejected'            => ['dot'=>'#dc2626','bar'=>'#f87171'],
                    ];
                @endphp
                <table class="status-table">
                    @foreach($distribution as $key => $item)
                    @php $pct = round(($item['count'] / $totalApps) * 100); @endphp
                    <tr>
                        <td style="width:30%;">
                            <span class="status-dot" style="background:{{ $statusColors[$key]['dot'] }}"></span>
                            <strong>{{ $item['label'] }}</strong>
                        </td>
                        <td style="width:40%;">
                            <div class="status-bar-wrap">
                                <div class="status-bar" style="width:{{ $pct }}%; background:{{ $statusColors[$key]['bar'] }}"></div>
                            </div>
                        </td>
                        <td style="width:15%; text-align:right; font-weight:700;">{{ $item['count'] }}</td>
                        <td style="width:15%; text-align:right; color:#94a3b8;">{{ $pct }}%</td>
                    </tr>
                    @endforeach
                </table>
            </div>

            {{-- Top 5 Lowongan --}}
            <div class="section" style="margin-bottom:0;">
                <div class="section-title">Top 5 Lowongan Terpopuler</div>
                @foreach($topJobs as $idx => $job)
                @php
                    $rankClass = $idx === 0 ? 'gold' : ($idx === 1 ? 'silver' : ($idx === 2 ? 'bronze' : ''));
                @endphp
                <div class="top-job-row">
                    <div class="top-job-rank {{ $rankClass }}">{{ $idx + 1 }}</div>
                    <div class="top-job-name">
                        {{ $job->position }}
                        <div class="top-job-company">{{ $job->company }}</div>
                    </div>
                    <div>
                        <div class="top-job-count">{{ $job->applications_count }}</div>
                        <div class="top-job-sub">lamaran</div>
                    </div>
                </div>
                @endforeach
                @if($topJobs->isEmpty())
                    <p style="color:#94a3b8; font-size:9pt; text-align:center; padding:20px 0;">Belum ada data</p>
                @endif
            </div>
        </div>

        {{-- ===== TREN BULANAN ===== --}}
        <div class="section">
            <div class="section-title">Tren Lamaran Masuk (12 Bulan Terakhir)</div>
            @php $maxTrend = max(array_values($trends) ?: [1]); @endphp
            <div class="trend-grid">
                @foreach($trends as $month => $count)
                @php $barW = $maxTrend > 0 ? round(($count / $maxTrend) * 100) : 0; @endphp
                <div class="trend-cell">
                    <div class="t-month">{{ $month }}</div>
                    <div class="t-val">{{ $count }}</div>
                    <div class="t-bar" style="width:{{ $barW }}%"></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ===== TABEL DETAIL LOWONGAN ===== --}}
        <div class="section">
            <div class="section-title">Detail Statistik Seluruh Lowongan Kerja</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:30%">Nama Posisi</th>
                        <th style="width:25%">Perusahaan</th>
                        <th style="width:18%">Batas Pendaftaran</th>
                        <th style="width:12%">Status</th>
                        <th style="width:10%">Lamaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobsReport as $i => $job)
                    <tr>
                        <td style="color:#94a3b8; font-weight:600;">{{ $i + 1 }}</td>
                        <td style="font-weight:700; color:#0f172a;">{{ $job->position }}</td>
                        <td style="color:#475569;">{{ $job->company }}</td>
                        <td style="color:#475569;">
                            {{ $job->application_deadline ? $job->application_deadline->format('d/m/Y') : 'Tanpa batas' }}
                        </td>
                        <td>
                            <span class="badge {{ $job->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                {{ $job->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td style="font-weight:800; color:#2563eb;">{{ $job->applications_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:20px; color:#94a3b8;">Belum ada data lowongan.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align:right; color:#475569;">Total Seluruh Lamaran:</td>
                        <td>{{ $summary['total_applications'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- ===== TABEL LAMARAN TERBARU ===== --}}
        <div class="section">
            <div class="section-title">10 Lamaran Terbaru</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pelamar</th>
                        <th>Posisi yang Dilamar</th>
                        <th>Tanggal Melamar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentApplications as $i => $app)
                    @php
                        $statusClass = match($app->status) {
                            'pending'             => 'badge-pending',
                            'reviewed'            => 'badge-reviewed',
                            'interview_scheduled' => 'badge-interview',
                            'accepted'            => 'badge-accepted',
                            'rejected'            => 'badge-rejected',
                            default               => 'badge-inactive',
                        };
                        $statusText = match($app->status) {
                            'pending'             => 'Menunggu',
                            'reviewed'            => 'Ditinjau',
                            'interview_scheduled' => 'Interview',
                            'accepted'            => 'Diterima',
                            'rejected'            => 'Ditolak',
                            default               => $app->status,
                        };
                    @endphp
                    <tr>
                        <td style="color:#94a3b8; font-weight:600;">{{ $i + 1 }}</td>
                        <td style="font-weight:600; color:#0f172a;">
                            {{ $app->user?->name ?? $app->applicant_name ?? '-' }}
                        </td>
                        <td style="color:#475569;">{{ $app->jobListing?->position ?? '-' }}</td>
                        <td style="color:#475569;">{{ $app->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">Belum ada lamaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== TANDA TANGAN & FOOTER ===== --}}
        <div class="signature-box">
            <div class="sig-item">
                <div class="sig-label">Dibuat Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Admin Sistem</div>
            </div>
            <div class="sig-item">
                <div class="sig-label">Diperiksa Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Supervisor HRD</div>
            </div>
            <div class="sig-item">
                <div class="sig-label">Disetujui Oleh</div>
                <div class="sig-line"></div>
                <div class="sig-name">Direktur Perusahaan</div>
            </div>
        </div>

        <div class="print-footer">
            <div class="footer-left">
                <div class="fl-title">{{ config('app.name', 'Sistem Rekruter') }}</div>
                <div>Dokumen ini digenerate secara otomatis oleh sistem pada {{ $generatedAt }} WIB</div>
                <div>Laporan bersifat rahasia dan hanya untuk keperluan internal perusahaan.</div>
            </div>
            <div class="footer-right">
                <div>Halaman 1 dari 1</div>
                <div style="margin-top:4px;">&#169; {{ date('Y') }} {{ config('app.name') }}</div>
            </div>
        </div>

    </div>{{-- end .content --}}
</div>{{-- end .page --}}

<script>
    // Auto-print saat halaman terbuka dari tombol cetak
    if (window.location.search.includes('autoprint=1')) {
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 600);
        });
    }
</script>

</body>
</html>