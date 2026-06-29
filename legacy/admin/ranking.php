<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';
require_once '../includes/evaluation_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$jobManager = new JobManager();
$evaluationManager = new EvaluationManager();

// Get job ID
$jobId = $_GET['job_id'] ?? null;
if (!$jobId) {
    header('Location: applications.php');
    exit();
}

// Get job details
$job = $jobManager->getJobById($jobId);
if (!$job) {
    header('Location: applications.php');
    exit();
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'recalculate_ranking') {
        if ($evaluationManager->calculateJobRankings($jobId)) {
            $successMessage = "Ranking berhasil dihitung ulang";
        } else {
            $errorMessage = "Gagal menghitung ulang ranking";
        }
    }
}

// Get rankings
$rankings = $evaluationManager->getJobRankings($jobId);
$stats = $evaluationManager->getEvaluationStats($jobId);

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Pelamar - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('rankings'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Ranking Pelamar</h1>
                                <p class="text-gray-600 mt-2">Hasil penilaian menggunakan metode SAW (Simple Additive Weighting)</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="flex space-x-3">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="recalculate_ranking">
                                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                            <i class="fas fa-calculator mr-2"></i>Hitung Ulang Ranking
                                        </button>
                                    </form>
                                    <a href="rankings.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                                    </a>
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
                    </div>

                    <!-- Alert Messages -->
                    <?php if(isset($successMessage)): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i><?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($errorMessage)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Job Info -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Lowongan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Posisi:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($job['position']); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Perusahaan:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($job['company']); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Lokasi:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($job['location']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Pelamar</p>
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

                    <!-- Rankings Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Hasil Ranking</h3>
                        </div>
                        
                        <?php if (empty($rankings)): ?>
                        <div class="p-8 text-center">
                            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Belum ada data ranking. Pastikan semua pelamar sudah dinilai.</p>
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelamar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skor SAW</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Hitung</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($rankings as $ranking): ?>
                                    <tr class="<?php echo $ranking['rank_position'] <= 3 ? 'bg-yellow-50' : ''; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <?php if($ranking['rank_position'] == 1): ?>
                                                    <i class="fas fa-trophy text-yellow-500 text-xl mr-2"></i>
                                                <?php elseif($ranking['rank_position'] == 2): ?>
                                                    <i class="fas fa-medal text-gray-400 text-xl mr-2"></i>
                                                <?php elseif($ranking['rank_position'] == 3): ?>
                                                    <i class="fas fa-award text-orange-500 text-xl mr-2"></i>
                                                <?php else: ?>
                                                    <span class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center text-xs font-semibold text-gray-600 mr-2">
                                                        <?php echo $ranking['rank_position']; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="text-lg font-bold text-gray-900">#<?php echo $ranking['rank_position']; ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($ranking['applicant_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($ranking['applicant_email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo number_format($ranking['saw_score'], 4); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo ($ranking['saw_score'] * 100); ?>%"></div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900"><?php echo number_format($ranking['saw_score'] * 100, 1); ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo formatDateIndonesian(date('Y-m-d', strtotime($ranking['calculated_at']))); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="evaluate_application.php?id=<?php echo $ranking['application_id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3" title="Lihat Penilaian">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="viewEvaluationDetail(<?php echo $ranking['application_id']; ?>)" class="text-green-600 hover:text-green-900" title="Detail Penilaian">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Detail Modal -->
    <div id="evaluationDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Detail Penilaian</h3>
                        <button onclick="closeEvaluationDetailModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="evaluation-detail-content">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewEvaluationDetail(applicationId) {
            // Show loading state
            const content = document.getElementById('evaluation-detail-content');
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2 text-gray-600">Memuat detail penilaian...</p></div>';
            document.getElementById('evaluationDetailModal').classList.remove('hidden');
            
            // Fetch evaluation details via AJAX
            fetch('get_evaluation_detail.php?application_id=' + applicationId)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        populateEvaluationDetail(data.evaluations, data.saw_score);
                    } else {
                        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">' + (data.message || 'Gagal memuat detail penilaian') + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">Terjadi kesalahan: ' + error.message + '</p></div>';
                });
        }

        function populateEvaluationDetail(evaluations, sawScore) {
            const content = document.getElementById('evaluation-detail-content');
            
            let evaluationHtml = `
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Skor SAW: ${(sawScore * 100).toFixed(2)}%</h4>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: ${sawScore * 100}%"></div>
                    </div>
                </div>
                
                <div class="space-y-4">
            `;
            
            evaluations.forEach(function(eval) {
                evaluationHtml += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="font-semibold text-gray-900">${eval.criteria_name}</h5>
                            <span class="text-sm text-blue-600">Bobot: ${eval.weight}%</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-2">
                            <div>
                                <span class="text-sm text-gray-600">Skor:</span>
                                <span class="font-medium">${eval.score}/${eval.max_value}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Penilai:</span>
                                <span class="font-medium">${eval.evaluator_name}</span>
                            </div>
                        </div>
                        ${eval.notes ? `
                        <div class="mt-2">
                            <span class="text-sm text-gray-600">Catatan:</span>
                            <p class="text-sm text-gray-700 mt-1">${eval.notes}</p>
                        </div>
                        ` : ''}
                    </div>
                `;
            });
            
            evaluationHtml += '</div>';
            content.innerHTML = evaluationHtml;
        }

        function closeEvaluationDetailModal() {
            document.getElementById('evaluationDetailModal').classList.add('hidden');
        }
    </script>
    </div>
</body>
</html>
