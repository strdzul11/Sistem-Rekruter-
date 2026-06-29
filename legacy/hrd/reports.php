<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';

// Cek apakah user sudah login dan role HRD
if(!isLoggedIn() || getUserRole() !== 'hrd') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$jobManager = new JobManager();
$applicationManager = new ApplicationManager();

// Get statistics
$jobStats = $jobManager->getJobStats();
$applicationStats = $applicationManager->getApplicationStats();

// Get applications by month
$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT 
                DATE_FORMAT(applied_at, '%Y-%m') as month,
                COUNT(*) as count
              FROM applications 
              WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(applied_at, '%Y-%m')
              ORDER BY month ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $monthlyApplications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $monthlyApplications = [];
}

// Get applications by job
try {
    $query = "SELECT 
                j.position,
                j.company,
                COUNT(a.id) as application_count
              FROM job_listings j
              LEFT JOIN applications a ON j.id = a.job_id
              GROUP BY j.id, j.position, j.company
              ORDER BY application_count DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $applicationsByJob = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $applicationsByJob = [];
}

// Get recent applications
$recentApplications = $applicationManager->getAllApplications();
$recentApplications = array_slice($recentApplications, 0, 10);

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - HRD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="applications.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-file-alt mr-3"></i>
                    Aplikasi
                </a>
                <a href="jobs.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-briefcase mr-3"></i>
                    Lowongan Kerja
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem Penilaian</p>
                </div>
                <a href="evaluations.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
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
                <a href="reports.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
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
                                <h1 class="text-3xl font-bold text-gray-900">Laporan HRD</h1>
                                <p class="text-gray-600 mt-2">Analisis aplikasi dan performa rekrutmen</p>
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

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100">
                                    <i class="fas fa-file-alt text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Aplikasi</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['total_applications']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Menunggu Review</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['pending_applications']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Diterima</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['accepted_applications']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100">
                                    <i class="fas fa-briefcase text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Lowongan Aktif</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $jobStats['active_jobs']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Monthly Applications Chart -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Trend Aplikasi (6 Bulan Terakhir)</h3>
                            <div class="relative h-64">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>

                        <!-- Applications by Status -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Status Aplikasi</h3>
                            <div class="relative h-64">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Reports -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Top Jobs -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Lowongan Paling Diminati</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <?php foreach(array_slice($applicationsByJob, 0, 5) as $job): ?>
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($job['position']); ?></p>
                                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($job['company']); ?></p>
                                        </div>
                                        <span class="text-sm font-semibold text-blue-600"><?php echo $job['application_count']; ?> aplikasi</span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Applications -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Aplikasi Terbaru</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <?php foreach($recentApplications as $app): ?>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['applicant_name']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($app['position']); ?></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <?php echo getStatusBadge($app['status']); ?>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($app['applied_at']))); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                        <div class="flex space-x-4">
                            <a href="applications.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-file-alt mr-2"></i>Kelola Aplikasi
                            </a>
                            <a href="jobs.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                <i class="fas fa-briefcase mr-2"></i>Kelola Lowongan
                            </a>
                            <button onclick="printReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <i class="fas fa-print mr-2"></i>Print Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            // Monthly Applications Chart
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (monthlyCanvas) {
                const monthlyCtx = monthlyCanvas.getContext('2d');
                new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: [
                            <?php foreach($monthlyApplications as $month): ?>
                            '<?php echo date('M Y', strtotime($month['month'] . '-01')); ?>',
                            <?php endforeach; ?>
                        ],
                        datasets: [{
                            label: 'Jumlah Aplikasi',
                            data: [
                                <?php foreach($monthlyApplications as $month): ?>
                                <?php echo $month['count']; ?>,
                                <?php endforeach; ?>
                            ],
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Status Chart
            const statusCanvas = document.getElementById('statusChart');
            if (statusCanvas) {
                const statusCtx = statusCanvas.getContext('2d');
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Menunggu', 'Ditinjau', 'Diterima', 'Ditolak'],
                        datasets: [{
                            data: [
                                <?php echo $applicationStats['pending_applications']; ?>,
                                <?php echo $applicationStats['reviewed_applications']; ?>,
                                <?php echo $applicationStats['accepted_applications']; ?>,
                                <?php echo $applicationStats['rejected_applications']; ?>
                            ],
                            backgroundColor: [
                                '#F59E0B',
                                '#3B82F6',
                                '#10B981',
                                '#EF4444'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
        });

        function printReport() {
            window.print();
        }
    </script>
</body>
</html>
