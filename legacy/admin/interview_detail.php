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

// Get interviewer details
$interviewer = $auth->getUserById($interview['interviewer_id']);

$user = $auth->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Interview - Admin</title>
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
                                <h1 class="text-3xl font-bold text-gray-900">Detail Interview</h1>
                                <p class="text-gray-600 mt-2">Informasi lengkap interview pelamar</p>
                            </div>
                            <a href="interviews.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </div>

                    <!-- Interview Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Applicant Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pelamar</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Nama</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Email</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['applicant_email']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Telepon</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['applicant_phone']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Posisi</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['position']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Perusahaan</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['company']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Interview Information -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Interview</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tanggal & Waktu</label>
                                        <p class="text-sm text-gray-900"><?php echo date('d M Y, H:i', strtotime($interview['interview_date'])); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tipe Interview</label>
                                        <span class="px-2 py-1 text-xs font-semibold <?php echo $interview['interview_type'] == 'video' ? 'text-blue-800 bg-blue-100' : 'text-green-800 bg-green-100'; ?> rounded-full">
                                            <?php echo ucfirst($interview['interview_type']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Status</label>
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
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Interviewer</label>
                                        <p class="text-sm text-gray-900"><?php echo htmlspecialchars($interviewer['name']); ?></p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Durasi</label>
                                        <p class="text-sm text-gray-900"><?php echo $interview['duration_minutes']; ?> menit</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($interview['notes'])): ?>
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Catatan</h3>
                            <p class="text-sm text-gray-700"><?php echo htmlspecialchars($interview['notes']); ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex space-x-4">
                            <a href="edit_interview.php?id=<?php echo $interview['id']; ?>" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-edit mr-2"></i>Edit Interview
                            </a>
                            <?php if($interview['status'] !== 'completed'): ?>
                            <button onclick="cancelInterview(<?php echo $interview['id']; ?>)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                                <i class="fas fa-times mr-2"></i>Batalkan Interview
                            </button>
                            <?php endif; ?>
                            <a href="interview_feedback.php?id=<?php echo $interview['id']; ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                <i class="fas fa-star mr-2"></i>Berikan Feedback
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cancelInterview(id) {
            if (confirm('Apakah Anda yakin ingin membatalkan interview ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'interviews.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="cancel_interview">
                    <input type="hidden" name="interview_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    </div>
</body>
</html>
