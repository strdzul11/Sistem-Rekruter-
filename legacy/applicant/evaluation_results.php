<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/evaluation_manager.php';

// Cek apakah user sudah login dan role applicant
if(!isLoggedIn() || getUserRole() !== 'applicant') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$evaluationManager = new EvaluationManager();

// Get applicant results
$userId = $_SESSION['user_id'];
$results = $evaluationManager->getApplicantResults($userId);

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penilaian - Pelamar</title>
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
                <a href="profile.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-user mr-3"></i>
                    Profil
                </a>
                <a href="evaluation_results.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-star mr-3"></i>
                    Hasil Penilaian
                </a>
            </nav>
            
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Hasil Penilaian</h1>
                                <p class="text-gray-600 mt-2">Lihat hasil penilaian aplikasi Anda menggunakan metode SAW</p>
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

                    <?php if (empty($results)): ?>
                    <!-- No Results -->
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <i class="fas fa-info-circle text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Belum Ada Hasil Penilaian</h3>
                        <p class="text-gray-600 mb-6">Aplikasi Anda belum dinilai atau proses penilaian masih berlangsung.</p>
                        <a href="../jobs.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-search mr-2"></i>Cari Lowongan Lain
                        </a>
                    </div>
                    <?php else: ?>
                    
                    <!-- Results Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Aplikasi</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo count($results); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-trophy text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Ranking Terbaik</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        #<?php echo min(array_column($results, 'rank_position')); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Skor Tertinggi</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format(max(array_column($results, 'saw_score')) * 100, 1); ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results List -->
                    <div class="space-y-6">
                        <?php foreach($results as $result): ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-6">
                                <!-- Job Info -->
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($result['position']); ?></h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($result['company']); ?> • <?php echo htmlspecialchars($result['location']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <div class="flex items-center">
                                            <?php if($result['rank_position'] == 1): ?>
                                                <i class="fas fa-trophy text-yellow-500 text-2xl mr-2"></i>
                                            <?php elseif($result['rank_position'] == 2): ?>
                                                <i class="fas fa-medal text-gray-400 text-2xl mr-2"></i>
                                            <?php elseif($result['rank_position'] == 3): ?>
                                                <i class="fas fa-award text-orange-500 text-2xl mr-2"></i>
                                            <?php endif; ?>
                                            <div>
                                                <p class="text-2xl font-bold text-gray-900">#<?php echo $result['rank_position']; ?></p>
                                                <p class="text-sm text-gray-600">dari <?php echo $result['total_applicants']; ?> pelamar</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Score Display -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Skor SAW</span>
                                        <span class="text-sm font-bold text-gray-900"><?php echo number_format($result['saw_score'] * 100, 2); ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-gradient-to-r from-blue-500 to-green-500 h-3 rounded-full transition-all duration-300" 
                                             style="width: <?php echo ($result['saw_score'] * 100); ?>%"></div>
                                    </div>
                                </div>

                                <!-- Performance Indicator -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="text-center p-3 rounded-lg <?php echo $result['rank_position'] <= 3 ? 'bg-green-50' : ($result['rank_position'] <= 10 ? 'bg-yellow-50' : 'bg-gray-50'); ?>">
                                        <p class="text-sm font-medium text-gray-600">Kategori</p>
                                        <p class="text-lg font-bold <?php echo $result['rank_position'] <= 3 ? 'text-green-600' : ($result['rank_position'] <= 10 ? 'text-yellow-600' : 'text-gray-600'); ?>">
                                            <?php 
                                            if ($result['rank_position'] <= 3) {
                                                echo "Sangat Baik";
                                            } elseif ($result['rank_position'] <= 10) {
                                                echo "Baik";
                                            } else {
                                                echo "Cukup";
                                            }
                                            ?>
                                        </p>
                                    </div>
                                    
                                    <div class="text-center p-3 rounded-lg bg-blue-50">
                                        <p class="text-sm font-medium text-gray-600">Skor Numerik</p>
                                        <p class="text-lg font-bold text-blue-600"><?php echo number_format($result['saw_score'], 4); ?></p>
                                    </div>
                                    
                                    <div class="text-center p-3 rounded-lg bg-purple-50">
                                        <p class="text-sm font-medium text-gray-600">Tanggal Penilaian</p>
                                        <p class="text-lg font-bold text-purple-600"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($result['calculated_at']))); ?></p>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="flex justify-end">
                                    <button onclick="viewDetailedEvaluation(<?php echo $result['application_id']; ?>)" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                        <i class="fas fa-eye mr-2"></i>Lihat Detail Penilaian
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Evaluation Modal -->
    <div id="detailedEvaluationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Detail Penilaian</h3>
                        <button onclick="closeDetailedEvaluationModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="detailed-evaluation-content">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewDetailedEvaluation(applicationId) {
            // Show loading state
            const content = document.getElementById('detailed-evaluation-content');
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2 text-gray-600">Memuat detail penilaian...</p></div>';
            document.getElementById('detailedEvaluationModal').classList.remove('hidden');
            
            // Fetch evaluation details via AJAX
            fetch('get_detailed_evaluation.php?application_id=' + applicationId)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        populateDetailedEvaluation(data.evaluations, data.saw_score, data.criteria_weights);
                    } else {
                        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">' + (data.message || 'Gagal memuat detail penilaian') + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">Terjadi kesalahan: ' + error.message + '</p></div>';
                });
        }

        function populateDetailedEvaluation(evaluations, sawScore, criteriaWeights) {
            const content = document.getElementById('detailed-evaluation-content');
            
            let evaluationHtml = `
                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Skor SAW Anda</h4>
                    <div class="flex items-center mb-2">
                        <span class="text-3xl font-bold text-blue-600 mr-4">${(sawScore * 100).toFixed(2)}%</span>
                        <div class="flex-1">
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-4 rounded-full" style="width: ${sawScore * 100}%"></div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600">Skor ini dihitung menggunakan metode SAW (Simple Additive Weighting) berdasarkan kriteria penilaian yang telah ditetapkan.</p>
                </div>
                
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Rincian Penilaian per Kriteria</h4>
                    <div class="space-y-4">
            `;
            
            // Group evaluations by criteria
            const criteriaGroups = {};
            evaluations.forEach(function(eval) {
                if (!criteriaGroups[eval.criteria_name]) {
                    criteriaGroups[eval.criteria_name] = [];
                }
                criteriaGroups[eval.criteria_name].push(eval);
            });
            
            Object.keys(criteriaGroups).forEach(function(criteriaName) {
                const evals = criteriaGroups[criteriaName];
                const avgScore = evals.reduce((sum, e) => sum + parseInt(e.score), 0) / evals.length;
                const weight = evals[0].weight;
                const maxValue = evals[0].max_value;
                const normalizedScore = avgScore / maxValue;
                const weightedScore = normalizedScore * (weight / 100);
                
                evaluationHtml += `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <h5 class="font-semibold text-gray-900">${criteriaName}</h5>
                            <span class="text-sm text-blue-600 font-medium">Bobot: ${weight}%</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                            <div class="text-center p-3 bg-gray-50 rounded">
                                <p class="text-sm text-gray-600">Skor Rata-rata</p>
                                <p class="text-lg font-bold text-gray-900">${avgScore.toFixed(1)}/${maxValue}</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded">
                                <p class="text-sm text-gray-600">Skor Ternormalisasi</p>
                                <p class="text-lg font-bold text-blue-600">${normalizedScore.toFixed(3)}</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded">
                                <p class="text-sm text-gray-600">Kontribusi SAW</p>
                                <p class="text-lg font-bold text-green-600">${weightedScore.toFixed(3)}</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600">Progress</span>
                                <span class="text-sm font-medium">${(normalizedScore * 100).toFixed(1)}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: ${normalizedScore * 100}%"></div>
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-600">
                            <p class="font-medium mb-1">Detail Penilaian:</p>
                            <div class="space-y-1">
                `;
                
                evals.forEach(function(eval) {
                    evaluationHtml += `
                        <div class="flex justify-between">
                            <span>Penilai: ${eval.evaluator_name}</span>
                            <span class="font-medium">${eval.score}/${eval.max_value}</span>
                        </div>
                    `;
                });
                
                evaluationHtml += `
                            </div>
                        </div>
                    </div>
                `;
            });
            
            evaluationHtml += `
                    </div>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="text-md font-semibold text-gray-900 mb-2">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>Penjelasan Metode SAW
                    </h4>
                    <p class="text-sm text-gray-700 mb-2">
                        Simple Additive Weighting (SAW) adalah metode pengambilan keputusan yang menghitung skor akhir dengan cara:
                    </p>
                    <ol class="text-sm text-gray-700 list-decimal list-inside space-y-1">
                        <li>Normalisasi setiap skor kriteria (skor/nilai maksimum)</li>
                        <li>Mengalikan skor ternormalisasi dengan bobot kriteria</li>
                        <li>Menjumlahkan semua hasil perkalian untuk mendapat skor SAW</li>
                    </ol>
                </div>
            `;
            
            content.innerHTML = evaluationHtml;
        }

        function closeDetailedEvaluationModal() {
            document.getElementById('detailedEvaluationModal').classList.add('hidden');
        }
    </script>
</body>
</html>
