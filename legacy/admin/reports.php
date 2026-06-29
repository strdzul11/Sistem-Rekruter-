<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$jobManager = new JobManager();
$applicationManager = new ApplicationManager();

// Get statistics
$jobStats = $jobManager->getJobStats();
$applicationStats = $applicationManager->getApplicationStats();

// Get all applications for detailed report
$allApplications = $applicationManager->getAllApplications();

// Get applications by month (for chart)
$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT 
                DATE_FORMAT(applied_at, '%Y-%m') as month,
                COUNT(*) as count
              FROM applications 
              WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
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

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('reports'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Laporan Sistem</h1>
                                <p class="text-gray-600 mt-2">Analisis dan statistik lengkap sistem rekrutmen</p>
                            </div>
                            
                            <!-- User Profile Section -->
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="text-xs text-gray-500">Administrator</p>
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
                                    <i class="fas fa-briefcase text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Lowongan</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $jobStats['total_jobs']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Lowongan Aktif</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $jobStats['active_jobs']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100">
                                    <i class="fas fa-file-alt text-yellow-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Aplikasi</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['total_applications']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100">
                                    <i class="fas fa-users text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pelamar Terdaftar</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['total_applications']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Monthly Applications Chart -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aplikasi per Bulan (12 Bulan Terakhir)</h3>
                            <div class="relative h-64">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>

                        <!-- Applications by Job Chart -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Aplikasi per Lowongan</h3>
                            <div class="relative h-64">
                                <canvas id="jobChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Reports -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Applications by Status -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Aplikasi berdasarkan Status</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium text-gray-900">Menunggu</span>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $applicationStats['pending_applications']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium text-gray-900">Ditinjau</span>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $applicationStats['reviewed_applications']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium text-gray-900">Diterima</span>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $applicationStats['accepted_applications']; ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div>
                                            <span class="text-sm font-medium text-gray-900">Ditolak</span>
                                        </div>
                                        <span class="text-sm text-gray-600"><?php echo $applicationStats['rejected_applications']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                    </div>

                    <!-- Export Options -->
                    <div class="mt-8 bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Laporan</h3>
                        <div class="flex space-x-4">
                            <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                                <i class="fas fa-file-pdf mr-2"></i>Export PDF
                            </button>
                            <button onclick="exportToExcel()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                <i class="fas fa-file-excel mr-2"></i>Export Excel
                            </button>
                            <button onclick="printReport()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <i class="fas fa-print mr-2"></i>Print
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

            // Applications by Job Chart
            const jobCanvas = document.getElementById('jobChart');
            if (jobCanvas) {
                const jobCtx = jobCanvas.getContext('2d');
                new Chart(jobCtx, {
                    type: 'bar',
                    data: {
                        labels: [
                            <?php foreach(array_slice($applicationsByJob, 0, 5) as $job): ?>
                            '<?php echo htmlspecialchars($job['position']); ?>',
                            <?php endforeach; ?>
                        ],
                        datasets: [{
                            label: 'Jumlah Aplikasi',
                            data: [
                                <?php foreach(array_slice($applicationsByJob, 0, 5) as $job): ?>
                                <?php echo $job['application_count']; ?>,
                                <?php endforeach; ?>
                            ],
                            backgroundColor: '#3B82F6',
                            borderRadius: 4
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
        });

        function exportToPDF() {
            alert('Fitur export PDF akan segera tersedia');
        }

        function exportToExcel() {
            alert('Fitur export Excel akan segera tersedia');
        }

        function printReport() {
            window.print();
        }
    </script>
    </div>
</body>
</html>
