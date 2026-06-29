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

// Handle interview schedule creation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_schedule') {
    $applicationId = $_POST['application_id'];
    $interviewerId = $_POST['interviewer_id'];
    $interviewType = $_POST['interview_type'];
    $interviewDate = $_POST['interview_date'];
    $duration = $_POST['duration'];
    $meetingLink = $_POST['meeting_link'];
    $meetingRoom = $_POST['meeting_room'];
    $notes = $_POST['notes'];
    
    // Create interview schedule directly
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO interview_schedules (application_id, interviewer_id, interview_type, interview_date, duration_minutes, meeting_link, meeting_room, notes) VALUES (:application_id, :interviewer_id, :interview_type, :interview_date, :duration, :meeting_link, :meeting_room, :notes)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':application_id', $applicationId);
        $stmt->bindParam(':interviewer_id', $interviewerId);
        $stmt->bindParam(':interview_type', $interviewType);
        $stmt->bindParam(':interview_date', $interviewDate);
        $stmt->bindParam(':duration', $duration);
        $stmt->bindParam(':meeting_link', $meetingLink);
        $stmt->bindParam(':meeting_room', $meetingRoom);
        $stmt->bindParam(':notes', $notes);
        
        if($stmt->execute()) {
            // Update application interview status
            $updateQuery = "UPDATE applications SET interview_status = 'scheduled' WHERE id = :application_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':application_id', $applicationId);
            $updateStmt->execute();
            
            $success = "Jadwal interview berhasil dibuat!";
        } else {
            $error = "Gagal membuat jadwal interview.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all interview schedules
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get interview schedules
    $query = "SELECT s.*, 
                a.applicant_name, a.applicant_email,
                j.position, j.company,
                u.name as interviewer_name
              FROM interview_schedules s
              LEFT JOIN applications a ON s.application_id = a.id
              LEFT JOIN job_listings j ON a.job_id = j.id
              LEFT JOIN users u ON s.interviewer_id = u.id
              ORDER BY s.interview_date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get applications
    $query = "SELECT a.*, 
                COALESCE(u.name, a.applicant_name) as applicant_name, 
                COALESCE(u.email, a.applicant_email) as applicant_email,
                j.position, j.company, j.location
              FROM applications a 
              LEFT JOIN users u ON a.user_id = u.id 
              LEFT JOIN job_listings j ON a.job_id = j.id
              ORDER BY a.applied_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get interviewers
    $query = "SELECT id, name, email, role FROM users WHERE role IN ('admin', 'hrd')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $interviewers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $schedules = [];
    $applications = [];
    $interviewers = [];
}

function getInterviewStatusBadge($status) {
    $badges = [
        'scheduled' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Terjadwal</span>',
        'confirmed' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Dikonfirmasi</span>',
        'completed' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>',
        'cancelled' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>',
        'rescheduled' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Diubah</span>'
    ];
    return $badges[$status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
}

function getInterviewTypeBadge($type) {
    $badges = [
        'phone' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Telepon</span>',
        'video' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Video</span>',
        'in-person' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Tatap Muka</span>',
        'online' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Online</span>'
    ];
    return $badges[$type] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Interview - Admin</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Jadwal Interview</h1>
                            <p class="text-gray-600 mt-2">Kelola jadwal interview kandidat</p>
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

                    <!-- Create Schedule Button -->
                    <div class="mb-6">
                        <button onclick="openCreateModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Buat Jadwal Interview
                        </button>
                    </div>

                    <!-- Interview Schedules Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Jadwal Interview</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kandidat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interviewer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if(empty($schedules)): ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            <i class="fas fa-calendar-times text-4xl mb-2"></i>
                                            <p>Belum ada jadwal interview</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($schedules as $schedule): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($schedule['applicant_name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($schedule['applicant_email']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($schedule['position']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($schedule['interviewer_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('d M Y, H:i', strtotime($schedule['interview_date'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getInterviewTypeBadge($schedule['interview_type']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getInterviewStatusBadge($schedule['status']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="interview_detail.php?id=<?php echo $schedule['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <a href="interview_feedback.php?id=<?php echo $schedule['id']; ?>" class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-comment"></i> Feedback
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Schedule Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Buat Jadwal Interview</h3>
                        <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="create_schedule">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Aplikasi *</label>
                                <select name="application_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih Aplikasi</option>
                                    <?php foreach($applications as $app): ?>
                                    <option value="<?php echo $app['id']; ?>">
                                        <?php echo htmlspecialchars($app['applicant_name']); ?> - <?php echo htmlspecialchars($app['position']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Interviewer *</label>
                                <select name="interviewer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih Interviewer</option>
                                    <?php foreach($interviewers as $interviewer): ?>
                                    <option value="<?php echo $interviewer['id']; ?>">
                                        <?php echo htmlspecialchars($interviewer['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Interview *</label>
                                    <select name="interview_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Tipe</option>
                                        <option value="phone">Telepon</option>
                                        <option value="video">Video Call</option>
                                        <option value="in-person">Tatap Muka</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (menit) *</label>
                                    <input type="number" name="duration" value="60" min="15" max="180" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal & Waktu *</label>
                                <input type="datetime-local" name="interview_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Link (untuk video/online)</label>
                                <input type="url" name="meeting_link" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="https://meet.google.com/...">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ruangan (untuk tatap muka)</label>
                                <input type="text" name="meeting_room" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ruang Meeting A">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan..."></textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-calendar-plus mr-2"></i>Buat Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }
    </script>
    </div>
</body>
</html>
