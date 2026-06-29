<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $job->position }} - {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</title>
    <link rel="icon" type="image/png" href="{{ asset($settings['company_logo'] ?? 'logo.png') }}">
    <meta name="description" content="Lamar posisi {{ $job->position }} di {{ $job->company }}. {{ Str::limit($job->description, 150) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .modal-backdrop { backdrop-filter: blur(6px); background: rgba(0,0,0,0.5); }
        .modal-enter { animation: modalIn 0.3s ease-out; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .tab-active { border-color: #3b82f6; color: #3b82f6; background-color: #eff6ff; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="" class="w-8 h-8 object-contain">
                        <h1 class="ml-3 text-xl font-bold text-gray-900">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</h1>
                    </a>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('jobs.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Semua Lowongan
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            <i class="fas fa-sign-in-alt mr-1"></i>Masuk
                        </a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                            <i class="fas fa-user-plus mr-1"></i>Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success / Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6 flex items-start animate__animated animate__fadeInDown">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold mb-1">Lamaran Berhasil Dikirim!</h4>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6 flex items-start animate__animated animate__fadeInDown">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="font-semibold mb-1">Gagal Melamar</h4>
                    <p class="text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <strong>Terjadi Kesalahan</strong>
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Job Detail Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <!-- Job Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-8 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold mb-2">{{ $job->position }}</h1>
                        <p class="text-blue-100 text-lg">{{ $job->company }}</p>
                    </div>
                    <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-semibold backdrop-blur-sm">
                        <span class="inline-block w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                        Aktif
                    </span>
                </div>
            </div>

            <!-- Job Meta -->
            <div class="px-8 py-6 border-b border-gray-100">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div class="flex items-center text-gray-600">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Lokasi</p>
                            <p class="text-sm font-medium">{{ $job->location }}</p>
                        </div>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-briefcase text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Tipe</p>
                            <p class="text-sm font-medium">{{ ucfirst($job->employment_type) }}</p>
                        </div>
                    </div>
                    @if(!empty($job->salary_range))
                        <div class="flex items-center text-gray-600">
                            <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-money-bill-wave text-yellow-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Gaji</p>
                                <p class="text-sm font-medium">{{ $job->salary_range }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-center text-gray-600">
                        <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calendar-alt text-purple-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Dipasang</p>
                            <p class="text-sm font-medium">{{ $job->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($job->application_deadline)
                        <div class="flex items-center text-gray-600">
                            <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-hourglass-half text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Batas Melamar</p>
                                <p class="text-sm font-medium text-red-600">{{ $job->application_deadline->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    @endif
                    @if($job->applicant_quota)
                        <div class="flex items-center text-gray-600">
                            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-users text-orange-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Sisa Kuota</p>
                                <p class="text-sm font-medium text-orange-600">{{ $job->remainingQuota() }} / {{ $job->applicant_quota }} Pelamar</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Job Description -->
            <div class="px-8 py-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-file-alt mr-2 text-blue-600"></i>Deskripsi Pekerjaan</h2>
                <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $job->description }}</div>
            </div>

            @if(!empty($job->requirements))
                <div class="px-8 py-6 border-t border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3"><i class="fas fa-clipboard-list mr-2 text-indigo-600"></i>Persyaratan</h2>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $job->requirements }}</div>
                </div>
            @endif
        </div>

        <!-- Apply Section — Two Options -->
        <div class="mb-12">
            <h2 class="text-xl font-bold text-gray-900 mb-6 text-center">Pilih Cara Melamar</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Option 1: Quick Apply (Guest) -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-transparent hover:border-blue-300 transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-bolt text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Lamar Langsung</h3>
                                <p class="text-sm text-gray-500">Tanpa perlu daftar akun</p>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-600 mb-6">
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Isi data diri langsung</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Upload CV & surat lamaran</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Cek status via email</li>
                            <li class="flex items-center text-gray-400"><i class="fas fa-times text-red-400 mr-2 text-xs"></i>Tidak bisa pantau dari dashboard</li>
                        </ul>
                        <button onclick="document.getElementById('quickApplyModal').classList.remove('hidden')" class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-6 py-3 rounded-xl hover:from-yellow-600 hover:to-orange-600 transition-all font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <i class="fas fa-paper-plane mr-2"></i>Lamar Sekarang
                        </button>
                    </div>
                </div>

                <!-- Option 2: Login & Apply -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-transparent hover:border-green-300 transition-all duration-300 overflow-hidden group">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Masuk & Lamar</h3>
                                <p class="text-sm text-gray-500">Login akun terlebih dahulu</p>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm text-gray-600 mb-6">
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Data otomatis dari profil</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Upload CV & surat lamaran</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Pantau status di dashboard</li>
                            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2 text-xs"></i>Jadwal interview langsung terlihat</li>
                        </ul>
                        @auth
                            <button onclick="document.getElementById('registeredApplyModal').classList.remove('hidden')" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-sign-in-alt mr-2"></i>Lamar Sekarang
                            </button>
                        @else
                            <a href="{{ route('login', ['redirect' => route('jobs.show', $job)]) }}" class="block w-full text-center bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-sign-in-alt mr-2"></i>Masuk untuk Melamar
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- Quick Apply Modal (Guest / Tanpa Login) -->
    <!-- ============================================ -->
    <div id="quickApplyModal" class="fixed inset-0 z-50 hidden modal-backdrop flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto modal-enter">
            <div class="sticky top-0 bg-white px-6 pt-6 pb-4 border-b border-gray-100 rounded-t-2xl z-10">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Lamar Langsung</h3>
                        <p class="text-sm text-gray-500">{{ $job->position }} — {{ $job->company }}</p>
                    </div>
                    <button onclick="document.getElementById('quickApplyModal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('jobs.quickApply', $job) }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Masukkan nama lengkap">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="contoh@email.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="08xxxxxxxxxx">
                    <p class="text-xs text-gray-400 mt-1">Kami akan menghubungi Anda melalui WhatsApp</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Umur</label>
                        <input type="number" name="age" value="{{ old('age') }}" min="18" max="65" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="25">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="">Pilih Gender</option>
                            <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pengalaman Kerja</label>
                    <select name="work_experience" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        <option value="">Pilih Pengalaman</option>
                        <option value="Fresh Graduate" {{ old('work_experience') == 'Fresh Graduate' ? 'selected' : '' }}>Fresh Graduate (0 tahun)</option>
                        <option value="1-2 tahun" {{ old('work_experience') == '1-2 tahun' ? 'selected' : '' }}>1-2 tahun</option>
                        <option value="3-5 tahun" {{ old('work_experience') == '3-5 tahun' ? 'selected' : '' }}>3-5 tahun</option>
                        <option value="6-10 tahun" {{ old('work_experience') == '6-10 tahun' ? 'selected' : '' }}>6-10 tahun</option>
                        <option value="10+ tahun" {{ old('work_experience') == '10+ tahun' ? 'selected' : '' }}>10+ tahun</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload CV <span class="text-red-500">*</span></label>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, DOC, DOCX. Maksimal 5MB</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Surat Lamaran (Opsional)</label>
                    <textarea name="cover_letter" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" placeholder="Ceritakan mengapa Anda tertarik dengan posisi ini...">{{ old('cover_letter') }}</textarea>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Proses Selanjutnya:</p>
                            <ul class="list-disc list-inside space-y-0.5 text-xs">
                                <li>Tim HR akan meninjau lamaran Anda</li>
                                <li>Kami akan menghubungi melalui WhatsApp</li>
                                <li>Anda dapat cek status di halaman <a href="{{ route('jobs.checkStatus') }}" class="underline">Cek Status</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="document.getElementById('quickApplyModal').classList.add('hidden')" class="px-5 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition font-medium">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition font-medium shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Lamaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- Registered Apply Modal (Logged-in User) -->
    <!-- ============================================ -->
    @auth
    <div id="registeredApplyModal" class="fixed inset-0 z-50 hidden modal-backdrop flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto modal-enter">
            <div class="sticky top-0 bg-white px-6 pt-6 pb-4 border-b border-gray-100 rounded-t-2xl z-10">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Lamar sebagai {{ Auth::user()->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $job->position }} — {{ $job->company }}</p>
                    </div>
                    <button onclick="document.getElementById('registeredApplyModal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('jobs.apply', $job) }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <!-- Pre-filled info -->
                <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-user text-gray-400 w-5 mr-2"></i>
                        <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <i class="fas fa-envelope text-gray-400 w-5 mr-2"></i>
                        <span class="text-gray-700">{{ Auth::user()->email }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload CV <span class="text-red-500">*</span></label>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition text-sm">
                    <p class="text-xs text-gray-400 mt-1">Format: PDF, DOC, DOCX. Maksimal 5MB</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Surat Lamaran (Opsional)</label>
                    <textarea name="cover_letter" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition" placeholder="Ceritakan mengapa Anda tertarik dengan posisi ini...">{{ old('cover_letter') }}</textarea>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex">
                        <i class="fas fa-shield-alt text-green-600 mt-0.5 mr-3"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-semibold">Keuntungan melamar dengan akun:</p>
                            <p class="text-xs mt-1">Status lamaran, jadwal interview, dan hasil evaluasi dapat dipantau langsung dari dashboard Anda.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" onclick="document.getElementById('registeredApplyModal').classList.add('hidden')" class="px-5 py-2.5 text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition font-medium">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition font-medium shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Lamaran
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="{{ asset($settings['company_logo'] ?? 'logo.png') }}" alt="" class="w-8 h-8 object-contain">
                    <span class="ml-3 font-bold">{{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}</span>
                </div>
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ $settings['company_name'] ?? 'PT. PUTRI KEBUN LESTARI' }}. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('quickApplyModal')?.classList.add('hidden');
                document.getElementById('registeredApplyModal')?.classList.add('hidden');
            }
        });

        // Close modals on backdrop click
        ['quickApplyModal', 'registeredApplyModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', function(e) {
                    if (e.target === this) this.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
