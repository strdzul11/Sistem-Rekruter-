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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'schedule_interview':
                $applicationId = intval($_POST['application_id']);
                $interviewerId = intval($_POST['interviewer_id']);
                $interviewDate = $_POST['interview_date'];
                $timezone = $_POST['timezone'];
                $interviewType = $_POST['interview_type'];
                $duration = intval($_POST['duration']);
                $notes = sanitizeInput($_POST['notes']);
                
                if ($interviewManager->scheduleInterview($applicationId, $interviewerId, $interviewDate, $timezone, $interviewType, $duration, $notes)) {
                    $successMessage = "Interview berhasil dijadwalkan";
                } else {
                    $errorMessage = "Gagal menjadwalkan interview";
                }
                break;
                
            case 'update_status':
                $interviewId = intval($_POST['interview_id']);
                $status = $_POST['status'];
                $meetingLink = $_POST['meeting_link'] ?? null;
                
                if ($interviewManager->updateInterviewStatus($interviewId, $status, $meetingLink)) {
                    $successMessage = "Status interview berhasil diupdate";
                } else {
                    $errorMessage = "Gagal mengupdate status interview";
                }
                break;
                
            case 'cancel_interview':
                $interviewId = intval($_POST['interview_id']);
                $reason = sanitizeInput($_POST['reason']);
                
                if ($interviewManager->cancelInterview($interviewId, $reason)) {
                    $successMessage = "Interview berhasil dibatalkan";
                } else {
                    $errorMessage = "Gagal membatalkan interview";
                }
                break;
                
            case 'reschedule_interview':
                $interviewId = intval($_POST['interview_id']);
                $newDate = $_POST['new_date'];
                $newTimezone = $_POST['new_timezone'];
                $reason = sanitizeInput($_POST['reason']);
                
                if ($interviewManager->rescheduleInterview($interviewId, $newDate, $newTimezone, $reason)) {
                    $successMessage = "Interview berhasil dijadwalkan ulang";
                } else {
                    $errorMessage = "Gagal menjadwalkan ulang interview";
                }
                break;
        }
    }
}

// Get all interviews - Admin can see all interviews
if (getUserRole() === 'admin') {
    $interviews = $interviewManager->getAllInterviews();
    $statistics = $interviewManager->getInterviewStatistics();
} else {
    $interviews = $interviewManager->getInterviewsForUser($_SESSION['user_id'], 'interviewer');
    $statistics = $interviewManager->getInterviewStatistics($_SESSION['user_id']);
}

// Get all applications for scheduling
$stmt = $db->prepare("
    SELECT a.*, j.position, j.company, u.name as applicant_name, u.email as applicant_email
    FROM applications a
    LEFT JOIN job_listings j ON a.job_id = j.id
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.status IN ('reviewed', 'accepted') AND a.interview_status = 'not_scheduled'
    ORDER BY a.applied_at DESC
");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Manajemen Interview - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                                <h1 class="text-3xl font-bold text-gray-900">Manajemen Interview</h1>
                                <p class="text-gray-600 mt-2">Kelola jadwal interview dan feedback pelamar</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <button onclick="openScheduleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-plus mr-2"></i>Jadwalkan Interview
                                </button>
                                
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

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fas fa-video text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Interview</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $statistics['total_interviews'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Selesai</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $statistics['completed_interviews'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-100 rounded-lg">
                                    <i class="fas fa-clock text-yellow-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Terjadwal</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo $statistics['scheduled_interviews'] ?? 0; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <i class="fas fa-star text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Rata-rata Skor</p>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo number_format($statistics['average_score'] ?? 0, 1); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interviews Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Interview</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelamar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($interviews as $interview): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($interview['applicant_name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($interview['applicant_email']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['position']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($interview['company']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?php echo date('d M Y', strtotime($interview['interview_date'])); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($interview['interview_date'])); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold <?php echo $interview['interview_type'] == 'video' ? 'text-blue-800 bg-blue-100' : 'text-green-800 bg-green-100'; ?> rounded-full">
                                                <?php echo ucfirst($interview['interview_type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $statusColors = [
                                                'scheduled' => 'text-yellow-800 bg-yellow-100',
                                                'confirmed' => 'text-blue-800 bg-blue-100',
                                                'completed' => 'text-green-800 bg-green-100',
                                                'cancelled' => 'text-red-800 bg-red-100',
                                                'rescheduled' => 'text-purple-800 bg-purple-100'
                                            ];
                                            $colorClass = $statusColors[$interview['status']] ?? 'text-gray-800 bg-gray-100';
                                            ?>
                                            <span class="px-2 py-1 text-xs font-semibold <?php echo $colorClass; ?> rounded-full">
                                                <?php echo ucfirst($interview['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="viewInterview(<?php echo $interview['id']; ?>)" class="text-blue-600 hover:text-blue-900" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button onclick="editInterview(<?php echo $interview['id']; ?>)" class="text-green-600 hover:text-green-900" title="Edit Interview">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="interview_responses.php?id=<?php echo $interview['id']; ?>" class="text-purple-600 hover:text-purple-900" title="View Responses">
                                                    <i class="fas fa-comments"></i>
                                                </a>
                                                <?php if($interview['status'] !== 'completed'): ?>
                                                <button onclick="cancelInterview(<?php echo $interview['id']; ?>)" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Interview Modal -->
    <div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwalkan Interview</h3>
                    <form method="POST" id="scheduleForm">
                        <input type="hidden" name="action" value="schedule_interview">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Aplikasi</label>
                                <select name="application_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih aplikasi...</option>
                                    <?php foreach($applications as $app): ?>
                                    <option value="<?php echo $app['id']; ?>">
                                        <?php echo htmlspecialchars($app['applicant_name'] ?? $app['applicant_name']); ?> - <?php echo htmlspecialchars($app['position']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Interviewer</label>
                                <select name="interviewer_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih interviewer...</option>
                                    <?php foreach($interviewers as $interviewer): ?>
                                    <option value="<?php echo $interviewer['id']; ?>">
                                        <?php echo htmlspecialchars($interviewer['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal & Waktu</label>
                                <input type="datetime-local" name="interview_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                                <select name="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                                    <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                                    <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Interview</label>
                                <select name="interview_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="video">Video Call</option>
                                    <option value="phone">Phone Call</option>
                                    <option value="in-person">In-Person</option>
                                    <option value="online">Online Test</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (menit)</label>
                                <input type="number" name="duration" value="60" min="15" max="180" step="15" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catatan tambahan untuk interview..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-calendar-plus mr-2"></i>Jadwalkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('hidden');
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        function viewInterview(id) {
            // Implement view interview functionality
            window.location.href = 'interview_detail.php?id=' + id;
        }

        function editInterview(id) {
            // Implement edit interview functionality
            window.location.href = 'edit_interview.php?id=' + id;
        }

        function cancelInterview(id) {
            if (confirm('Apakah Anda yakin ingin membatalkan interview ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="cancel_interview">
                    <input type="hidden" name="interview_id" value="${id}">
                    <input type="hidden" name="reason" value="Dibatalkan oleh admin">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    </div>
</body>
</html>
