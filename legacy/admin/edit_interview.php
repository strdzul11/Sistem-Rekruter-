<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/interview_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$interviewManager = new InterviewManager();
$auth = new Auth();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get interview ID from URL
$interviewId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($interviewId <= 0) {
    header('Location: interviews.php');
    exit();
}

// Get interview details
$interview = $interviewManager->getInterviewById($interviewId);

if (!$interview) {
    header('Location: interviews.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_interview') {
        $interviewDate = $_POST['interview_date'];
        $interviewType = $_POST['interview_type'];
        $duration = intval($_POST['duration']);
        $timezone = $_POST['timezone'];
        $notes = sanitizeInput($_POST['notes']);
        $meetingLink = sanitizeInput($_POST['meeting_link']);
        
        if ($interviewManager->updateInterview($interviewId, $interviewDate, $interviewType, $duration, $timezone, $notes, $meetingLink)) {
            $successMessage = "Interview berhasil diperbarui";
            // Refresh interview data
            $interview = $interviewManager->getInterviewById($interviewId);
        } else {
            $errorMessage = "Gagal memperbarui interview";
        }
    }
}

// Get all interviewers
$stmt = $db->prepare("SELECT id, name, email FROM users WHERE role IN ('admin', 'hrd') ORDER BY name");
$stmt->execute();
$interviewers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user = $auth->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Interview - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('interviews'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Edit Interview</h1>
                                <p class="text-gray-600 mt-2">Ubah jadwal dan informasi interview</p>
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

                    <!-- Edit Form -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_interview">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Interview Date & Time -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal & Waktu Interview</label>
                                    <input type="datetime-local" name="interview_date" value="<?php echo date('Y-m-d\TH:i', strtotime($interview['interview_date'])); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>

                                <!-- Interview Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Interview</label>
                                    <select name="interview_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="video" <?php echo $interview['interview_type'] == 'video' ? 'selected' : ''; ?>>Video Call</option>
                                        <option value="phone" <?php echo $interview['interview_type'] == 'phone' ? 'selected' : ''; ?>>Telepon</option>
                                        <option value="in-person" <?php echo $interview['interview_type'] == 'in-person' ? 'selected' : ''; ?>>Tatap Muka</option>
                                        <option value="online" <?php echo $interview['interview_type'] == 'online' ? 'selected' : ''; ?>>Online</option>
                                    </select>
                                </div>

                                <!-- Duration -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (menit)</label>
                                    <input type="number" name="duration" value="<?php echo $interview['duration_minutes']; ?>" min="15" max="180" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                </div>

                                <!-- Timezone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                    <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        <option value="Asia/Jakarta" <?php echo $interview['timezone'] == 'Asia/Jakarta' ? 'selected' : ''; ?>>Asia/Jakarta (WIB)</option>
                                        <option value="Asia/Makassar" <?php echo $interview['timezone'] == 'Asia/Makassar' ? 'selected' : ''; ?>>Asia/Makassar (WITA)</option>
                                        <option value="Asia/Jayapura" <?php echo $interview['timezone'] == 'Asia/Jayapura' ? 'selected' : ''; ?>>Asia/Jayapura (WIT)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Meeting Link -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Link (untuk video call)</label>
                                <input type="url" name="meeting_link" value="<?php echo htmlspecialchars($interview['meeting_link']); ?>" placeholder="https://meet.google.com/..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <!-- Notes -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                                <textarea name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Catatan tambahan untuk interview..."><?php echo htmlspecialchars($interview['notes']); ?></textarea>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-8 flex space-x-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                                <a href="interview_detail.php?id=<?php echo $interview['id']; ?>" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                    <i class="fas fa-eye mr-2"></i>Lihat Detail
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
