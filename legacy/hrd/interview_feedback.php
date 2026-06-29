<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/interview_manager.php';

// Cek apakah user sudah login dan role hrd
if(!isLoggedIn() || getUserRole() !== 'hrd') {
    header('Location: ../auth/login.php');
    exit();
}

$interviewManager = new InterviewManager();
$auth = new Auth();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'submit_feedback':
                $interviewId = intval($_POST['interview_id']);
                $interviewerId = $_SESSION['user_id'];
                
                // Validate required fields
                $communicationScore = intval($_POST['communication_score']);
                $technicalScore = intval($_POST['technical_score']);
                $problemSolvingScore = intval($_POST['problem_solving_score']);
                $culturalFitScore = intval($_POST['cultural_fit_score']);
                $recommendation = $_POST['recommendation'];
                
                // Check if all required scores are provided (1-5)
                if ($communicationScore < 1 || $communicationScore > 5 ||
                    $technicalScore < 1 || $technicalScore > 5 ||
                    $problemSolvingScore < 1 || $problemSolvingScore > 5 ||
                    $culturalFitScore < 1 || $culturalFitScore > 5 ||
                    empty($recommendation)) {
                    $errorMessage = "Semua field yang bertanda * harus diisi dengan skor 1-5";
                } else {
                    $feedbackData = [
                        'applicant_id' => $_POST['applicant_id'] ?? null,
                        'communication_score' => $communicationScore,
                        'technical_score' => $technicalScore,
                        'problem_solving_score' => $problemSolvingScore,
                        'cultural_fit_score' => $culturalFitScore,
                        'strengths' => sanitizeInput($_POST['strengths']),
                        'weaknesses' => sanitizeInput($_POST['weaknesses']),
                        'areas_for_improvement' => sanitizeInput($_POST['areas_for_improvement']),
                        'recommendation' => $recommendation,
                        'next_steps' => sanitizeInput($_POST['next_steps']),
                        'additional_notes' => sanitizeInput($_POST['additional_notes'])
                    ];
                    
                    // Debug logging
                    error_log("HRD Feedback Debug - Interview ID: " . $interviewId);
                    error_log("HRD Feedback Debug - Interviewer ID: " . $interviewerId);
                    error_log("HRD Feedback Debug - Feedback Data: " . print_r($feedbackData, true));
                    
                    $result = $interviewManager->submitInterviewFeedback($interviewId, $interviewerId, $feedbackData);
                    error_log("HRD Feedback Debug - Result: " . ($result ? 'SUCCESS' : 'FAILED'));
                    
                    if ($result) {
                        $successMessage = "Feedback interview berhasil disimpan";
                    } else {
                        $errorMessage = "Gagal menyimpan feedback interview. Silakan cek log error untuk detail.";
                    }
                }
                break;
        }
    }
}

// Get interview ID from URL
$interviewId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$interviewId) {
    header('Location: interviews.php');
    exit();
}

// Get interview details
$interview = $interviewManager->getInterviewDetails($interviewId);
if (!$interview) {
    header('Location: interviews.php');
    exit();
}

// Get existing feedback if any
$existingFeedback = $interviewManager->getInterviewFeedback($interviewId);

$user = $auth->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Feedback - HRD</title>
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
                <a href="rankings.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-trophy mr-3"></i>
                    Ranking & Hasil
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Interview</p>
                </div>
                <a href="interviews.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
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
                                <h1 class="text-3xl font-bold text-gray-900">Interview Feedback</h1>
                                <p class="text-gray-600 mt-2">Form penilaian interview untuk <?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="interviews.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
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

                    <!-- Interview Information -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Interview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Pelamar</p>
                                <p class="font-medium"><?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Posisi</p>
                                <p class="font-medium"><?php echo htmlspecialchars($interview['position']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Interview</p>
                                <p class="font-medium"><?php echo date('d M Y H:i', strtotime($interview['interview_date'])); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Interviewer</p>
                                <p class="font-medium"><?php echo htmlspecialchars($interview['interviewer_name']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Form Penilaian Interview</h3>
                        
                        <?php if($existingFeedback): ?>
                            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                                <i class="fas fa-info-circle mr-2"></i>Feedback sudah pernah disubmit. Anda dapat mengupdate feedback di bawah ini.
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="feedbackForm">
                            <input type="hidden" name="action" value="submit_feedback">
                            <input type="hidden" name="interview_id" value="<?php echo $interviewId; ?>">
                            <input type="hidden" name="applicant_id" value="<?php echo $interview['applicant_id'] ?? ''; ?>">
                            
                            <!-- Scoring Section -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Penilaian Skor (1-5)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Komunikasi</label>
                                        <select name="communication_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih skor...</option>
                                            <option value="1" <?php echo ($existingFeedback['communication_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                            <option value="2" <?php echo ($existingFeedback['communication_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                            <option value="3" <?php echo ($existingFeedback['communication_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                            <option value="4" <?php echo ($existingFeedback['communication_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                            <option value="5" <?php echo ($existingFeedback['communication_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kemampuan Teknis</label>
                                        <select name="technical_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih skor...</option>
                                            <option value="1" <?php echo ($existingFeedback['technical_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                            <option value="2" <?php echo ($existingFeedback['technical_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                            <option value="3" <?php echo ($existingFeedback['technical_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                            <option value="4" <?php echo ($existingFeedback['technical_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                            <option value="5" <?php echo ($existingFeedback['technical_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Problem Solving</label>
                                        <select name="problem_solving_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih skor...</option>
                                            <option value="1" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                            <option value="2" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                            <option value="3" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                            <option value="4" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                            <option value="5" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Cultural Fit</label>
                                        <select name="cultural_fit_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih skor...</option>
                                            <option value="1" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                            <option value="2" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                            <option value="3" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                            <option value="4" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                            <option value="5" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Overall Score Display -->
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Skor Rata-rata:</span>
                                        <span id="overallScore" class="text-lg font-bold text-blue-600">0.0</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detailed Feedback -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Feedback Detail</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kelebihan</label>
                                        <textarea name="strengths" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Sebutkan kelebihan dan kekuatan kandidat..."><?php echo htmlspecialchars($existingFeedback['strengths'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kelemahan</label>
                                        <textarea name="weaknesses" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Sebutkan kelemahan yang perlu diperbaiki..."><?php echo htmlspecialchars($existingFeedback['weaknesses'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Area untuk Pengembangan</label>
                                        <textarea name="areas_for_improvement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Sebutkan area yang perlu dikembangkan..."><?php echo htmlspecialchars($existingFeedback['areas_for_improvement'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recommendation -->
                            <div class="mb-8">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Rekomendasi</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi</label>
                                        <select name="recommendation" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih rekomendasi...</option>
                                            <option value="strong_hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'strong_hire' ? 'selected' : ''; ?>>Strong Hire - Sangat Direkomendasikan</option>
                                            <option value="hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'hire' ? 'selected' : ''; ?>>Hire - Direkomendasikan</option>
                                            <option value="maybe" <?php echo ($existingFeedback['recommendation'] ?? '') == 'maybe' ? 'selected' : ''; ?>>Maybe - Pertimbangkan</option>
                                            <option value="no_hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'no_hire' ? 'selected' : ''; ?>>No Hire - Tidak Direkomendasikan</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Langkah Selanjutnya</label>
                                        <input type="text" name="next_steps" value="<?php echo htmlspecialchars($existingFeedback['next_steps'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Contoh: Interview dengan manager, tes teknis, dll">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Notes -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                                <textarea name="additional_notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan atau observasi khusus..."><?php echo htmlspecialchars($existingFeedback['additional_notes'] ?? ''); ?></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-3">
                                <a href="interviews.php" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                    Batal
                                </a>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calculate overall score
        function calculateOverallScore() {
            const scores = [
                parseInt(document.querySelector('select[name="communication_score"]').value) || 0,
                parseInt(document.querySelector('select[name="technical_score"]').value) || 0,
                parseInt(document.querySelector('select[name="problem_solving_score"]').value) || 0,
                parseInt(document.querySelector('select[name="cultural_fit_score"]').value) || 0
            ];
            
            const validScores = scores.filter(score => score > 0);
            if (validScores.length === 4) {
                const average = validScores.reduce((sum, score) => sum + score, 0) / validScores.length;
                document.getElementById('overallScore').textContent = average.toFixed(1);
            } else {
                document.getElementById('overallScore').textContent = '0.0';
            }
        }
        
        // Add event listeners to score selects
        document.querySelectorAll('select[name$="_score"]').forEach(select => {
            select.addEventListener('change', calculateOverallScore);
        });
        
        // Calculate initial score if form is pre-filled
        document.addEventListener('DOMContentLoaded', calculateOverallScore);
    </script>
</body>
</html>
