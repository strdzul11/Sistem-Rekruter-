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
$recentApplications = $applicationManager->getAllApplications();
$recentJobs = $jobManager->getAllJobs();

// Handle potential false returns and limit recent data
$recentApplications = is_array($recentApplications) ? array_slice($recentApplications, 0, 5) : [];
$recentJobs = is_array($recentJobs) ? array_slice($recentJobs, 0, 5) : [];

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PT. PUTRI KEBUN LESTARI</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('dashboard'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8 flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
                            <p class="text-gray-600 mt-2">Selamat datang kembali, <?php echo htmlspecialchars($user['name']); ?>!</p>
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

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Jobs -->
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

                        <!-- Active Jobs -->
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

                        <!-- Total Applications -->
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

                        <!-- Pending Applications -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100">
                                    <i class="fas fa-clock text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Menunggu Review</p>
                                    <p class="text-2xl font-semibold text-gray-900"><?php echo $applicationStats['pending_applications']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Application Status Chart -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Aplikasi</h3>
                            <div class="relative h-64">
                                <canvas id="applicationChart"></canvas>
                            </div>
                        </div>

                        <!-- Job Status Chart -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Lowongan</h3>
                            <div class="relative h-64">
                                <canvas id="jobChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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
                                <div class="mt-4">
                                    <a href="applications.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Lihat semua aplikasi →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Jobs -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Lowongan Terbaru</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <?php foreach($recentJobs as $job): ?>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($job['position']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($job['company']); ?></p>
                                        </div>
                                        <div class="text-right">
                                            <?php echo getStatusBadge($job['status']); ?>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($job['created_at']))); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-4">
                                    <a href="jobs.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Lihat semua lowongan →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Chart.js is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            // Application Status Chart
            const applicationCanvas = document.getElementById('applicationChart');
            if (applicationCanvas) {
                const applicationCtx = applicationCanvas.getContext('2d');
                new Chart(applicationCtx, {
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

            // Job Status Chart
            const jobCanvas = document.getElementById('jobChart');
            if (jobCanvas) {
                const jobCtx = jobCanvas.getContext('2d');
                new Chart(jobCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Aktif', 'Tidak Aktif', 'Ditutup'],
                        datasets: [{
                            data: [
                                <?php echo $jobStats['active_jobs']; ?>,
                                <?php echo $jobStats['inactive_jobs']; ?>,
                                <?php echo $jobStats['closed_jobs']; ?>
                            ],
                            backgroundColor: [
                                '#10B981',
                                '#6B7280',
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
    </script>
    </div>
</body>
</html>
