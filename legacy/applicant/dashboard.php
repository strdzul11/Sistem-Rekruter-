<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';

// Cek apakah user sudah login dan role applicant
if(!isLoggedIn() || getUserRole() !== 'applicant') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$jobManager = new JobManager();
$applicationManager = new ApplicationManager();

// Handle job application
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_job') {
    $jobId = $_POST['job_id'];
    $coverLetter = sanitizeInput($_POST['cover_letter']);
    
    // Handle resume upload
    $resumePath = null;
    if(isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if(!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        
        if(in_array($fileExtension, $allowedExtensions)) {
            $fileName = uniqid() . '_' . $_FILES['resume']['name'];
            $filePath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                $resumePath = $fileName;
            }
        }
    }
    
    if($applicationManager->applyForJobWithResume($_SESSION['user_id'], $jobId, $coverLetter, $resumePath)) {
        $success = "Aplikasi berhasil dikirim!";
    } else {
        $error = "Gagal mengirim aplikasi atau Anda sudah pernah melamar posisi ini.";
    }
}

// Get all active jobs
$jobs = $jobManager->getAllJobs('active');

// Get user's applications
$userApplications = $applicationManager->getApplicationsByUser($_SESSION['user_id']);

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelamar - PT. PUTRI KEBUN LESTARI</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <img src="../logo.png" alt="PT. PUTRI KEBUN LESTARI" class="w-10 h-10 object-contain">
                    <h1 class="ml-3 text-xl font-bold text-gray-900">PT. PUTRI KEBUN LESTARI</h1>
                </div>
            </div>
            
            <nav class="mt-6">
                <div class="px-6 py-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu Utama</p>
                </div>
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="profile.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-user mr-3"></i>
                    Profil
                </a>
                <a href="evaluation_results.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-star mr-3"></i>
                    Hasil Penilaian
                </a>
                <a href="my_interviews.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-comments mr-3"></i>
                    My Interviews
                </a>
            </nav>
            
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Selamat Datang, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p class="text-gray-600 mt-2">Temukan lowongan kerja yang sesuai dengan keahlian Anda</p>
                </div>
                
                <!-- User Profile Section -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                            <p class="text-xs text-gray-500">Pelamar</p>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="flex items-center text-gray-600 hover:text-gray-900 text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('jobs')" id="jobs-tab" class="py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                        <i class="fas fa-briefcase mr-2"></i>Lowongan Kerja
                    </button>
                    <button onclick="showTab('applications')" id="applications-tab" class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i class="fas fa-file-alt mr-2"></i>Aplikasi Saya
                    </button>
                </nav>
            </div>
        </div>

        <!-- Jobs Tab -->
        <div id="jobs-content" class="tab-content">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Lowongan Kerja Tersedia</h2>
                <p class="text-gray-600">Pilih lowongan kerja yang sesuai dengan minat dan keahlian Anda</p>
            </div>

            <!-- Jobs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($jobs as $job): ?>
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($job['position']); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($job['company']); ?></p>
                            </div>
                            <?php echo getStatusBadge($job['status']); ?>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                <?php echo htmlspecialchars($job['location']); ?>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-briefcase mr-2"></i>
                                <?php echo ucfirst($job['employment_type']); ?>
                            </div>
                            <?php if($job['salary_range']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-money-bill-wave mr-2"></i>
                                <?php echo htmlspecialchars($job['salary_range']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-sm text-gray-700 mb-4 line-clamp-3">
                            <?php echo substr(htmlspecialchars($job['description']), 0, 120) . '...'; ?>
                        </p>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                <?php echo formatDateIndonesian(date('Y-m-d', strtotime($job['created_at']))); ?>
                            </span>
                            <button onclick="openJobModal(<?php echo htmlspecialchars(json_encode($job)); ?>)" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm">
                                <i class="fas fa-eye mr-1"></i>Lihat Detail
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Applications Tab -->
        <div id="applications-content" class="tab-content hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Aplikasi Saya</h2>
                <p class="text-gray-600">Pantau status aplikasi yang telah Anda kirim</p>
            </div>

            <!-- Applications Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Aplikasi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lowongan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Apply</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if(empty($userApplications)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Belum ada aplikasi yang dikirim</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($userApplications as $app): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['position']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($app['company']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($app['location']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo getStatusBadge($app['status']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($app['applied_at']))); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Detail Modal -->
    <div id="jobModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 id="job-title" class="text-xl font-semibold text-gray-900"></h3>
                        <button onclick="closeJobModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="job-details" class="space-y-4 mb-6">
                        <!-- Job details will be populated by JavaScript -->
                    </div>
                    
                    <form method="POST" id="application-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="apply_job">
                        <input type="hidden" name="job_id" id="apply_job_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload CV/Resume *</label>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX. Maksimal 5MB</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Surat Lamaran (Opsional)</label>
                                <textarea name="cover_letter" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Tulis surat lamaran Anda di sini..."></textarea>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium">Informasi Tambahan:</p>
                                        <p class="mt-1">Data profil Anda (nama, email, telepon, alamat) akan digunakan untuk aplikasi ini. Pastikan informasi profil Anda sudah lengkap dan akurat.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeJobModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-paper-plane mr-2"></i>Kirim Aplikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('[id$="-tab"]').forEach(tab => {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active class to selected tab
            document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
            document.getElementById(tabName + '-tab').classList.add('border-blue-500', 'text-blue-600');
        }

        function openJobModal(job) {
            document.getElementById('job-title').textContent = job.position;
            document.getElementById('apply_job_id').value = job.id;
            
            const jobDetails = document.getElementById('job-details');
            jobDetails.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Perusahaan</label>
                        <p class="text-gray-900">${job.company}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Lokasi</label>
                        <p class="text-gray-900">${job.location}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Jenis Pekerjaan</label>
                        <p class="text-gray-900">${job.employment_type.charAt(0).toUpperCase() + job.employment_type.slice(1)}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Gaji</label>
                        <p class="text-gray-900">${job.salary_range || 'Tidak disebutkan'}</p>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Deskripsi Pekerjaan</label>
                    <p class="text-gray-900 whitespace-pre-line">${job.description}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Persyaratan</label>
                    <p class="text-gray-900 whitespace-pre-line">${job.requirements}</p>
                </div>
            `;
            
            document.getElementById('jobModal').classList.remove('hidden');
        }

        function closeJobModal() {
            document.getElementById('jobModal').classList.add('hidden');
        }
    </script>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
