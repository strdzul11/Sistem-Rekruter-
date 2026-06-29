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
$jobManager = new JobManager();
$evaluationManager = new EvaluationManager();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'recalculate_all_rankings') {
        $jobs = $jobManager->getAllJobs();
        $successCount = 0;
        foreach ($jobs as $job) {
            if ($evaluationManager->calculateJobRankings($job['id'])) {
                $successCount++;
            }
        }
        $successMessage = "Berhasil menghitung ulang ranking untuk $successCount lowongan";
    }
}

// Get all jobs with ranking data
$database = new Database();
$db = $database->getConnection();

$query = "SELECT j.*, 
            COUNT(DISTINCT a.id) as total_applications,
            COUNT(DISTINCT ar.id) as ranked_applications,
            MAX(ar.updated_at) as last_ranking_update
          FROM job_listings j
          LEFT JOIN applications a ON j.id = a.job_id
          LEFT JOIN application_rankings ar ON j.id = ar.job_id AND ar.evaluation_status = 'final'
          GROUP BY j.id
          HAVING total_applications > 0
          ORDER BY j.created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$jobsWithRankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking & Hasil - Admin</title>
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
                <a href="evaluations.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-star mr-3"></i>
                    Penilaian Pelamar
                </a>
                <a href="rankings.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
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
                                <h1 class="text-3xl font-bold text-gray-900">Ranking & Hasil Penilaian</h1>
                                <p class="text-gray-600 mt-2">Lihat hasil ranking pelamar berdasarkan metode SAW</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="recalculate_all_rankings">
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                        <i class="fas fa-calculator mr-2"></i>Hitung Ulang Semua Ranking
                                    </button>
                                </form>
                                
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
                    </div>

                    <!-- Alert Messages -->
                    <?php if(isset($successMessage)): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i><?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Jobs with Rankings -->
                    <div class="space-y-6">
                        <?php foreach($jobsWithRankings as $job): ?>
                        <?php 
                        $rankings = $evaluationManager->getJobRankings($job['id']);
                        $stats = $evaluationManager->getEvaluationStats($job['id']);
                        ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($job['position']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($job['company']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="ranking.php?job_id=<?php echo $job['id']; ?>" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm">
                                            <i class="fas fa-eye mr-1"></i>Detail Ranking
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Statistics -->
                            <div class="px-6 py-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-blue-600"><?php echo $job['total_applications']; ?></div>
                                        <div class="text-sm text-gray-600">Total Pelamar</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600"><?php echo $stats['evaluated_applications'] ?? 0; ?></div>
                                        <div class="text-sm text-gray-600">Sudah Dinilai</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-yellow-600"><?php echo $job['ranked_applications']; ?></div>
                                        <div class="text-sm text-gray-600">Sudah Diranking</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php if($job['last_ranking_update']): ?>
                                                Update Terakhir
                                            <?php else: ?>
                                                Belum Ada Ranking
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            <?php if($job['last_ranking_update']): ?>
                                                <?php echo formatDateIndonesian(date('Y-m-d', strtotime($job['last_ranking_update']))); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Top 3 Rankings -->
                            <?php if (!empty($rankings)): ?>
                            <div class="px-6 py-4">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Top 3 Pelamar Terbaik</h4>
                                <div class="space-y-3">
                                    <?php foreach(array_slice($rankings, 0, 3) as $ranking): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="flex items-center mr-4">
                                                <?php if($ranking['rank_position'] == 1): ?>
                                                    <i class="fas fa-trophy text-yellow-500 text-xl mr-2"></i>
                                                <?php elseif($ranking['rank_position'] == 2): ?>
                                                    <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                <?php elseif($ranking['rank_position'] == 3): ?>
                                                    <i class="fas fa-award text-orange-500 text-xl mr-2"></i>
                                                <?php endif; ?>
                                                <span class="text-lg font-bold text-gray-900">#<?php echo $ranking['rank_position']; ?></span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ranking['applicant_name']); ?></div>
                                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ranking['applicant_email']); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold text-blue-600"><?php echo number_format($ranking['saw_score'] * 100, 1); ?>%</div>
                                            <div class="text-xs text-gray-500">Skor SAW</div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if(count($rankings) > 3): ?>
                                <div class="mt-4 text-center">
                                    <a href="ranking.php?job_id=<?php echo $job['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Lihat Semua Ranking (<?php echo count($rankings); ?> pelamar) →
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <div class="px-6 py-8 text-center">
                                <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600">Belum ada ranking. Pastikan pelamar sudah dinilai.</p>
                                <a href="evaluations.php" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-star mr-2"></i>Mulai Penilaian
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if(empty($jobsWithRankings)): ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-trophy text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Data Ranking</h3>
                        <p class="text-gray-600 mb-6">Belum ada lowongan yang memiliki aplikasi untuk diranking.</p>
                        <a href="jobs.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Buat Lowongan Baru
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
