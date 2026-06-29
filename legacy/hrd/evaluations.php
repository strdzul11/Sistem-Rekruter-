<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';
require_once '../includes/evaluation_manager.php';

// Cek apakah user sudah login dan role HRD
if(!isLoggedIn() || getUserRole() !== 'hrd') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$applicationManager = new ApplicationManager();
$evaluationManager = new EvaluationManager();
$jobManager = new JobManager();

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$jobFilter = $_GET['job_id'] ?? '';

// Get all applications with filters
$applications = $applicationManager->getAllApplications($statusFilter ?: null, $jobFilter ?: null);
$applications = is_array($applications) ? $applications : [];

// Get all jobs for filter
$jobs = $jobManager->getAllJobs();

// Get evaluation stats
$stats = $evaluationManager->getEvaluationStats();

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Pelamar - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <div class="w-64 bg-white shadow-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-briefcase text-white"></i>
                    </div>
                    <h1 class="ml-3 text-xl font-bold text-gray-900">PT. PUTRI KEBUN LESTARI</h1>
                </div>
            </div>
            
            <nav class="mt-6">
                <div class="px-6 py-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu Utama</p>
                </div>
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                <a href="jobs.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-briefcase mr-3"></i>
                    Lowongan Kerja
                </a>
                <a href="applications.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-file-alt mr-3"></i>
                    Aplikasi
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem Penilaian</p>
                </div>
                <a href="evaluations.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-star mr-3"></i>
                    Penilaian Pelamar
                </a>
                <a href="rankings.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-trophy mr-3"></i>
                    Ranking & Hasil
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Interview</p>
                </div>
                <a href="interviews.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-video mr-3"></i>
                    Manajemen Interview
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lainnya</p>
                </div>
                <a href="reports.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-chart-bar mr-3"></i>
                    Laporan
                </a>
            </nav>
            
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Penilaian Pelamar</h1>
                                <p class="text-gray-600 mt-2">Berikan penilaian untuk setiap pelamar menggunakan metode SAW</p>
                            </div>
                            
                            <!-- User Profile Section -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="text-xs text-gray-500">HRD</p>
                                    </div>
                                </div>
                                <a href="../auth/logout.php" class="flex items-center text-gray-600 hover:text-gray-900 text-sm">
                                    <i class="fas fa-sign-out-alt mr-2"></i>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Aplikasi</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_applications'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-star text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Sudah Dinilai</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['evaluated_applications'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-trophy text-yellow-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Sudah Diranking</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $stats['ranked_applications'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Aplikasi</h3>
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="reviewed" <?php echo $statusFilter === 'reviewed' ? 'selected' : ''; ?>>Ditinjau</option>
                                    <option value="accepted" <?php echo $statusFilter === 'accepted' ? 'selected' : ''; ?>>Diterima</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                    <option value="interview_scheduled" <?php echo $statusFilter === 'interview_scheduled' ? 'selected' : ''; ?>>Wawancara</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lowongan Kerja</label>
                                <select name="job_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Semua Lowongan</option>
                                    <?php foreach($jobs as $job): ?>
                                    <option value="<?php echo $job['id']; ?>" <?php echo $jobFilter == $job['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($job['position']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-filter mr-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Applications Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Pelamar untuk Dinilai</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelamar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lowongan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Penilaian</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Apply</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($applications as $app): ?>
                                    <?php 
                                    $isEvaluated = $evaluationManager->isApplicationFullyEvaluated($app['id']);
                                    $sawScore = $isEvaluated ? $evaluationManager->calculateSAWScore($app['id']) : 0;
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['applicant_email']); ?></div>
                                                    <?php if($app['applicant_age'] || $app['applicant_gender'] || $app['work_experience']): ?>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        <?php if($app['applicant_age']): ?>
                                                            Umur: <?php echo $app['applicant_age']; ?> tahun
                                                        <?php endif; ?>
                                                        <?php if($app['applicant_gender']): ?>
                                                            <?php echo $app['applicant_age'] ? ' | ' : ''; ?><?php echo $app['applicant_gender']; ?>
                                                        <?php endif; ?>
                                                        <?php if($app['work_experience']): ?>
                                                            <?php echo ($app['applicant_age'] || $app['applicant_gender']) ? ' | ' : ''; ?><?php echo $app['work_experience']; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['position']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['company']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getStatusBadge($app['status']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($isEvaluated): ?>
                                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
                                                    <i class="fas fa-check mr-1"></i>Sudah Dinilai
                                                </span>
                                                <div class="text-xs text-gray-500 mt-1">SAW: <?php echo number_format($sawScore * 100, 1); ?>%</div>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">
                                                    <i class="fas fa-clock mr-1"></i>Belum Dinilai
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo formatDateIndonesian(date('Y-m-d', strtotime($app['applied_at']))); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="evaluate_application.php?id=<?php echo $app['id']; ?>" 
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                                                <i class="fas fa-star mr-2"></i>
                                                <?php echo $isEvaluated ? 'Edit Penilaian' : 'Beri Penilaian'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
