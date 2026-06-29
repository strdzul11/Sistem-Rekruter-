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

// Get application ID
$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header('Location: applications.php');
    exit();
}

// Get application details
$application = $applicationManager->getApplicationById($applicationId);
if (!$application) {
    header('Location: applications.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'save_evaluation') {
        $success = true;
        $evaluatorId = $_SESSION['user_id'];
        
        foreach ($_POST['scores'] as $criteriaId => $score) {
            $notes = $_POST['notes'][$criteriaId] ?? '';
            if (!$evaluationManager->saveEvaluation($applicationId, $criteriaId, $score, $evaluatorId, $notes)) {
                $success = false;
            }
        }
        
        if ($success) {
            $successMessage = "Penilaian berhasil disimpan";
            
            // Hitung ulang ranking untuk job ini
            $evaluationManager->calculateJobRankings($application['job_id']);
        } else {
            $errorMessage = "Gagal menyimpan penilaian";
        }
    }
}

// Get criteria and existing evaluations
$criteria = $evaluationManager->getActiveCriteria();
$existingEvaluations = $evaluationManager->getApplicationEvaluations($applicationId, $_SESSION['user_id']);

// Convert existing evaluations to associative array for easier access
$evaluationData = [];
foreach ($existingEvaluations as $eval) {
    $evaluationData[$eval['criteria_id']] = $eval;
}

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Aplikasi - Admin</title>
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
                    <h1 class="ml-3 text-xl font-bold text-gray-900">PT. Clarajob</h1>
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
                <a href="applications.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-file-alt mr-3"></i>
                    Aplikasi
                </a>
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
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Penilaian Aplikasi</h1>
                                <p class="text-gray-600 mt-2">Berikan penilaian untuk pelamar menggunakan metode SAW</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="applications.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                                </a>
                                
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
                    
                    <?php if(isset($errorMessage)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Application Info -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Aplikasi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Pelamar:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($application['applicant_name']); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Email:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($application['applicant_email']); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Posisi:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($application['position']); ?></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Perusahaan:</span>
                                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($application['company']); ?></p>
                            </div>
                        </div>
                        
                        <?php if($application['resume_path']): ?>
                        <div class="mt-4">
                            <span class="text-sm font-medium text-gray-600">CV:</span>
                            <div class="mt-2">
                                <a href="download_cv.php?file=<?php echo urlencode(basename($application['resume_path'])); ?>" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-download mr-2"></i>Download CV
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Evaluation Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Form Penilaian</h3>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="save_evaluation">
                            
                            <?php foreach($criteria as $criterion): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="mb-4">
                                    <h4 class="text-md font-semibold text-gray-900"><?php echo htmlspecialchars($criterion['name']); ?></h4>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($criterion['description']); ?></p>
                                    <p class="text-xs text-blue-600 mt-1">Bobot: <?php echo $criterion['weight']; ?>%</p>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Skor (<?php echo $criterion['min_value']; ?>-<?php echo $criterion['max_value']; ?>)
                                        </label>
                                        <select name="scores[<?php echo $criterion['id']; ?>]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih Skor</option>
                                            <?php for($i = $criterion['min_value']; $i <= $criterion['max_value']; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo (isset($evaluationData[$criterion['id']]) && $evaluationData[$criterion['id']]['score'] == $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> - <?php 
                                                    $labels = [1 => 'Sangat Kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'];
                                                    echo $labels[$i] ?? $i;
                                                ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                        <textarea name="notes[<?php echo $criterion['id']; ?>]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Tambahkan catatan penilaian..."><?php echo isset($evaluationData[$criterion['id']]) ? htmlspecialchars($evaluationData[$criterion['id']]['notes']) : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                                <a href="applications.php" class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                    Batal
                                </a>
                                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Penilaian
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
