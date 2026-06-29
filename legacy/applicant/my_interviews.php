<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/interview_manager.php';

// Cek apakah user sudah login dan role applicant
if(!isLoggedIn() || getUserRole() !== 'applicant') {
    header('Location: ../auth/login.php');
    exit();
}

$interviewManager = new InterviewManager();
$auth = new Auth();

// Get user's interviews
$userId = $_SESSION['user_id'];
$interviews = [];

try {
    $db = new Database();
    $stmt = $db->getConnection()->prepare("
        SELECT 
            i.*,
            a.applicant_name,
            a.applicant_email,
            j.position,
            j.company,
            u.name as interviewer_name,
            u.email as interviewer_email,
            COUNT(ir.id) as response_count
        FROM interview_schedules i
        LEFT JOIN applications a ON i.application_id = a.id
        LEFT JOIN job_listings j ON a.job_id = j.id
        LEFT JOIN users u ON i.interviewer_id = u.id
        LEFT JOIN interview_responses ir ON i.id = ir.interview_schedule_id
        WHERE a.user_id = ?
        GROUP BY i.id
        ORDER BY i.interview_date DESC
    ");
    $stmt->execute([$userId]);
    $interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error getting user interviews: " . $e->getMessage());
}

$user = $auth->getUserById($_SESSION['user_id']);

// Helper function for interview status badge
function getInterviewStatusBadge($status) {
    $colors = [
        'scheduled' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'rescheduled' => 'bg-purple-100 text-purple-800'
    ];
    $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . ucfirst($status) . '</span>';
}

// Helper function for interview type badge
function getInterviewTypeBadge($type) {
    $colors = [
        'video' => 'bg-blue-100 text-blue-800',
        'phone' => 'bg-green-100 text-green-800',
        'in-person' => 'bg-purple-100 text-purple-800',
        'online' => 'bg-orange-100 text-orange-800'
    ];
    $color = $colors[$type] ?? 'bg-gray-100 text-gray-800';
    return '<span class="px-2 py-1 text-xs font-medium rounded-full ' . $color . '">' . ucfirst($type) . '</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Interviews - Pelamar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <h1 class="text-3xl font-bold text-gray-900">My Interviews</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                            <p class="text-xs text-gray-500">Pelamar</p>
                        </div>
                        <div class="h-8 w-8 bg-orange-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                <li><a href="dashboard.php" class="text-gray-500 hover:text-gray-700"><i class="fas fa-home"></i></a></li>
                <li><i class="fas fa-chevron-right text-gray-400"></i></li>
                <li><span class="text-gray-900 font-medium">My Interviews</span></li>
            </ol>
        </nav>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-calendar text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Interviews</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($interviews); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo count(array_filter($interviews, function($i) { return $i['status'] === 'completed'; })); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Scheduled</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo count(array_filter($interviews, function($i) { return in_array($i['status'], ['scheduled', 'confirmed']); })); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-comments text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">With Responses</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo count(array_filter($interviews, function($i) { return $i['response_count'] > 0; })); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interviews List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Interview History</h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($interviews)): ?>
                <div class="px-6 py-8 text-center">
                    <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No interviews scheduled yet</p>
                    <p class="text-sm text-gray-400 mt-2">Interviews will appear here once scheduled</p>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($interviews as $interview): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($interview['position']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($interview['company']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($interview['interview_date'])); ?></div>
                                <div class="text-sm text-gray-500"><?php echo date('H:i', strtotime($interview['interview_date'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getInterviewTypeBadge($interview['interview_type']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getInterviewStatusBadge($interview['status']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    <?php echo $interview['response_count']; ?> response<?php echo $interview['response_count'] != 1 ? 's' : ''; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <?php if ($interview['response_count'] > 0): ?>
                                    <a href="interview_responses.php?id=<?php echo $interview['id']; ?>" 
                                       class="text-purple-600 hover:text-purple-900" title="View Responses">
                                        <i class="fas fa-comments"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($interview['status'] === 'completed'): ?>
                                    <a href="evaluation_results.php" 
                                       class="text-green-600 hover:text-green-900" title="View Results">
                                        <i class="fas fa-star"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($interview['interview_type'] === 'video' && $interview['meeting_link']): ?>
                                    <a href="<?php echo htmlspecialchars($interview['meeting_link']); ?>" 
                                       target="_blank" class="text-blue-600 hover:text-blue-900" title="Join Meeting">
                                        <i class="fas fa-video"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
