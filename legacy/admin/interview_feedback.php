<?php
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';
require_once '../config/database.php';

// Check if user is logged in and has admin/hrd role
if(!isLoggedIn() || !in_array(getUserRole(), ['admin', 'hrd'])) {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$jobManager = new JobManager();

// Get interview schedule ID from URL
$scheduleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$scheduleId) {
    header('Location: interview_schedules.php');
    exit();
}

// Get interview schedule details
// Temporary workaround - get schedule data directly
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT s.*, 
                a.applicant_name, a.applicant_email, a.user_id as applicant_id,
                j.position, j.company,
                u.name as interviewer_name
              FROM interview_schedules s
              LEFT JOIN applications a ON s.application_id = a.id
              LEFT JOIN job_listings j ON a.job_id = j.id
              LEFT JOIN users u ON s.interviewer_id = u.id
              WHERE s.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $scheduleId);
    $stmt->execute();
    
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$schedule) {
        header('Location: interview_schedules.php');
        exit();
    }
} catch(Exception $e) {
    die('Error getting schedule: ' . $e->getMessage());
}

// Handle feedback submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    $communicationScore = (int)$_POST['communication_score'];
    $technicalScore = (int)$_POST['technical_score'];
    $problemSolvingScore = (int)$_POST['problem_solving_score'];
    $culturalFitScore = (int)$_POST['cultural_fit_score'];
    $strengths = sanitizeInput($_POST['strengths']);
    $weaknesses = sanitizeInput($_POST['weaknesses']);
    $areasForImprovement = sanitizeInput($_POST['areas_for_improvement']);
    $recommendation = $_POST['recommendation'];
    $nextSteps = sanitizeInput($_POST['next_steps']);
    $additionalNotes = sanitizeInput($_POST['additional_notes']);
    
    // Submit feedback directly
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if feedback already exists
        $checkQuery = "SELECT id FROM interview_feedback WHERE interview_schedule_id = :schedule_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':schedule_id', $scheduleId);
        $checkStmt->execute();
        
        $applicantId = $schedule['applicant_id'];
        
        if($checkStmt->rowCount() > 0) {
            // Update existing feedback
            $query = "UPDATE interview_feedback SET 
                        communication_score = :communication_score,
                        technical_score = :technical_score,
                        problem_solving_score = :problem_solving_score,
                        cultural_fit_score = :cultural_fit_score,
                        strengths = :strengths,
                        weaknesses = :weaknesses,
                        areas_for_improvement = :areas_for_improvement,
                        recommendation = :recommendation,
                        next_steps = :next_steps,
                        additional_notes = :additional_notes,
                        feedback_status = 'submitted'
                      WHERE interview_schedule_id = :schedule_id";
        } else {
            // Insert new feedback
            $query = "INSERT INTO interview_feedback (
                        interview_schedule_id, interviewer_id, applicant_id,
                        communication_score, technical_score, problem_solving_score, cultural_fit_score,
                        strengths, weaknesses, areas_for_improvement, recommendation, next_steps, additional_notes,
                        feedback_status
                      ) VALUES (
                        :schedule_id, :interviewer_id, :applicant_id,
                        :communication_score, :technical_score, :problem_solving_score, :cultural_fit_score,
                        :strengths, :weaknesses, :areas_for_improvement, :recommendation, :next_steps, :additional_notes,
                        'submitted'
                      )";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':schedule_id', $scheduleId);
        $stmt->bindParam(':interviewer_id', $_SESSION['user_id']);
        $stmt->bindParam(':applicant_id', $applicantId);
        $stmt->bindParam(':communication_score', $communicationScore);
        $stmt->bindParam(':technical_score', $technicalScore);
        $stmt->bindParam(':problem_solving_score', $problemSolvingScore);
        $stmt->bindParam(':cultural_fit_score', $culturalFitScore);
        $stmt->bindParam(':strengths', $strengths);
        $stmt->bindParam(':weaknesses', $weaknesses);
        $stmt->bindParam(':areas_for_improvement', $areasForImprovement);
        $stmt->bindParam(':recommendation', $recommendation);
        $stmt->bindParam(':next_steps', $nextSteps);
        $stmt->bindParam(':additional_notes', $additionalNotes);
        
        if($stmt->execute()) {
            // Update interview schedule status
            $updateQuery = "UPDATE interview_schedules SET status = 'completed' WHERE id = :schedule_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':schedule_id', $scheduleId);
            $updateStmt->execute();
            
            $success = "Feedback interview berhasil disimpan!";
        } else {
            $error = "Gagal menyimpan feedback interview.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get existing feedback if any
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM interview_feedback WHERE interview_schedule_id = :schedule_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':schedule_id', $scheduleId);
    $stmt->execute();
    
    $existingFeedback = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $existingFeedback = false;
}

function getRecommendationBadge($recommendation) {
    $badges = [
        'hire' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Hire</span>',
        'strong_hire' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Strong Hire</span>',
        'maybe' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Maybe</span>',
        'no_hire' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">No Hire</span>'
    ];
    return $badges[$recommendation] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Feedback - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('interviews'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8 flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Interview Feedback</h1>
                            <p class="text-gray-600 mt-2">Berikan feedback untuk interview kandidat</p>
                        </div>
                        
                        <!-- User Profile Section -->
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo ucfirst(getUserRole()); ?></p>
                                </div>
                            </div>
                            <a href="../auth/logout.php" class="flex items-center text-gray-600 hover:text-gray-900 text-sm">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Logout
                            </a>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if(isset($success)): ?>
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($error)): ?>
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Interview Details -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Detail Interview</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-900">Kandidat</h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($schedule['applicant_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($schedule['applicant_email']); ?></p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Posisi</h4>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($schedule['position']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($schedule['company']); ?></p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Tanggal & Waktu</h4>
                                    <p class="text-gray-600"><?php echo date('d M Y, H:i', strtotime($schedule['interview_date'])); ?></p>
                                    <p class="text-sm text-gray-500">Durasi: <?php echo $schedule['duration_minutes']; ?> menit</p>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Tipe Interview</h4>
                                    <p class="text-gray-600"><?php echo ucfirst($schedule['interview_type']); ?></p>
                                    <?php if($schedule['meeting_link']): ?>
                                    <p class="text-sm text-blue-600"><a href="<?php echo $schedule['meeting_link']; ?>" target="_blank">Meeting Link</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback Form -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Form Feedback Interview</h3>
                        </div>
                        <div class="p-6">
                            <form method="POST">
                                <input type="hidden" name="action" value="submit_feedback">
                                
                                <!-- Scoring Section -->
                                <div class="mb-8">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Penilaian (1-5)</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Komunikasi *</label>
                                            <select name="communication_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Pilih Skor</option>
                                                <option value="1" <?php echo ($existingFeedback['communication_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                                <option value="2" <?php echo ($existingFeedback['communication_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                                <option value="3" <?php echo ($existingFeedback['communication_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                                <option value="4" <?php echo ($existingFeedback['communication_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                                <option value="5" <?php echo ($existingFeedback['communication_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Teknis *</label>
                                            <select name="technical_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Pilih Skor</option>
                                                <option value="1" <?php echo ($existingFeedback['technical_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                                <option value="2" <?php echo ($existingFeedback['technical_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                                <option value="3" <?php echo ($existingFeedback['technical_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                                <option value="4" <?php echo ($existingFeedback['technical_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                                <option value="5" <?php echo ($existingFeedback['technical_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Problem Solving *</label>
                                            <select name="problem_solving_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Pilih Skor</option>
                                                <option value="1" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                                <option value="2" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                                <option value="3" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                                <option value="4" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                                <option value="5" <?php echo ($existingFeedback['problem_solving_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Cultural Fit *</label>
                                            <select name="cultural_fit_score" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                <option value="">Pilih Skor</option>
                                                <option value="1" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 1 ? 'selected' : ''; ?>>1 - Sangat Kurang</option>
                                                <option value="2" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 2 ? 'selected' : ''; ?>>2 - Kurang</option>
                                                <option value="3" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 3 ? 'selected' : ''; ?>>3 - Cukup</option>
                                                <option value="4" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 4 ? 'selected' : ''; ?>>4 - Baik</option>
                                                <option value="5" <?php echo ($existingFeedback['cultural_fit_score'] ?? '') == 5 ? 'selected' : ''; ?>>5 - Sangat Baik</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detailed Feedback -->
                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kelebihan Kandidat</label>
                                        <textarea name="strengths" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jelaskan kelebihan yang dimiliki kandidat..."><?php echo htmlspecialchars($existingFeedback['strengths'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kelemahan Kandidat</label>
                                        <textarea name="weaknesses" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jelaskan kelemahan yang perlu diperhatikan..."><?php echo htmlspecialchars($existingFeedback['weaknesses'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Area untuk Pengembangan</label>
                                        <textarea name="areas_for_improvement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Sebutkan area yang perlu dikembangkan..."><?php echo htmlspecialchars($existingFeedback['areas_for_improvement'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi *</label>
                                        <select name="recommendation" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Pilih Rekomendasi</option>
                                            <option value="strong_hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'strong_hire' ? 'selected' : ''; ?>>Strong Hire</option>
                                            <option value="hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'hire' ? 'selected' : ''; ?>>Hire</option>
                                            <option value="maybe" <?php echo ($existingFeedback['recommendation'] ?? '') == 'maybe' ? 'selected' : ''; ?>>Maybe</option>
                                            <option value="no_hire" <?php echo ($existingFeedback['recommendation'] ?? '') == 'no_hire' ? 'selected' : ''; ?>>No Hire</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Langkah Selanjutnya</label>
                                        <textarea name="next_steps" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jelaskan langkah selanjutnya untuk kandidat..."><?php echo htmlspecialchars($existingFeedback['next_steps'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Tambahan</label>
                                        <textarea name="additional_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan tentang interview..."><?php echo htmlspecialchars($existingFeedback['additional_notes'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end space-x-3 mt-8">
                                    <a href="interview_schedules.php" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                        Kembali
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
    </div>
    </div>
</body>
</html>