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

// Handle form submission
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_response':
                $questionId = intval($_POST['question_id']);
                $responseText = trim($_POST['response_text']);
                $score = intval($_POST['score']);
                $notes = trim($_POST['notes']);
                
                if ($questionId && $responseText && $score >= 1 && $score <= 5) {
                    if ($interviewManager->saveInterviewResponse($interviewId, $questionId, $responseText, $score, $notes)) {
                        $successMessage = "Response berhasil disimpan";
                    } else {
                        $errorMessage = "Gagal menyimpan response";
                    }
                } else {
                    $errorMessage = "Data tidak valid";
                }
                break;
                
            case 'delete_response':
                $responseId = intval($_POST['response_id']);
                if ($responseId && $interviewManager->deleteInterviewResponse($responseId)) {
                    $successMessage = "Response berhasil dihapus";
                } else {
                    $errorMessage = "Gagal menghapus response";
                }
                break;
        }
    }
}

// Get interview responses
$responses = $interviewManager->getInterviewResponses($interviewId);
$responseStats = $interviewManager->getResponseStatistics($interviewId);

// Get all questions for dropdown
$db = new Database();
$questions = [];
try {
    $stmt = $db->getConnection()->prepare("
        SELECT id, question_text, question_type, difficulty_level 
        FROM interview_questions 
        WHERE is_active = 1 
        ORDER BY question_type, difficulty_level
    ");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error loading questions: " . $e->getMessage());
}

$user = $auth->getUserById($_SESSION['user_id']);

// Helper function for score badge
function getScoreBadge($score) {
    if ($score >= 4) return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Excellent (' . $score . ')</span>';
    if ($score >= 3) return '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Good (' . $score . ')</span>';
    if ($score >= 2) return '<span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Fair (' . $score . ')</span>';
    return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Poor (' . $score . ')</span>';
}

// Helper function for question type badge
function getQuestionTypeBadge($type) {
    $colors = [
        'general' => 'bg-blue-100 text-blue-800',
        'technical' => 'bg-green-100 text-green-800',
        'behavioral' => 'bg-purple-100 text-purple-800',
        'situational' => 'bg-yellow-100 text-yellow-800',
        'cultural' => 'bg-pink-100 text-pink-800'
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
    <title>Interview Responses - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <h1 class="text-3xl font-bold text-gray-900">Interview Responses</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                            <p class="text-xs text-gray-500">Admin</p>
                        </div>
                        <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center">
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
                <li><a href="interviews.php" class="text-gray-500 hover:text-gray-700">Interviews</a></li>
                <li><i class="fas fa-chevron-right text-gray-400"></i></li>
                <li><span class="text-gray-900 font-medium">Responses</span></li>
            </ol>
        </nav>

        <!-- Interview Info -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Interview Details</h2>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Applicant</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Position</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($interview['position']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Interview Date</label>
                        <p class="text-gray-900"><?php echo date('d M Y H:i', strtotime($interview['interview_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($successMessage): ?>
        <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
        <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800"><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add Response Form -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Add Response</h3>
            </div>
            <div class="px-6 py-4">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_response">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Question</label>
                            <select name="question_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Question</option>
                                <?php foreach ($questions as $question): ?>
                                <option value="<?php echo $question['id']; ?>">
                                    <?php echo htmlspecialchars($question['question_text']); ?> 
                                    (<?php echo ucfirst($question['question_type']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Score (1-5)</label>
                            <select name="score" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Score</option>
                                <option value="1">1 - Poor</option>
                                <option value="2">2 - Fair</option>
                                <option value="3">3 - Good</option>
                                <option value="4">4 - Very Good</option>
                                <option value="5">5 - Excellent</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Response Text</label>
                            <textarea name="response_text" required rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter the candidate's response..."></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>Save Response
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Response Statistics -->
        <?php if (!empty($responseStats)): ?>
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Response Statistics</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($responseStats as $stat): ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo ucfirst($stat['question_type']); ?> Questions</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo $stat['total_responses']; ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Avg Score</p>
                                <p class="text-lg font-semibold text-blue-600"><?php echo number_format($stat['average_score'], 1); ?></p>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between text-xs text-gray-500">
                            <span>Good: <?php echo $stat['good_responses']; ?></span>
                            <span>Poor: <?php echo $stat['poor_responses']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Responses List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Interview Responses</h3>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($responses)): ?>
                <div class="px-6 py-8 text-center">
                    <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No responses recorded yet</p>
                </div>
                <?php else: ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($responses as $response): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs">
                                    <?php echo htmlspecialchars(substr($response['question_text'], 0, 100)) . (strlen($response['question_text']) > 100 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getQuestionTypeBadge($response['question_type']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs">
                                    <?php echo htmlspecialchars(substr($response['response_text'], 0, 100)) . (strlen($response['response_text']) > 100 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo getScoreBadge($response['score']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs">
                                    <?php echo htmlspecialchars(substr($response['notes'], 0, 50)) . (strlen($response['notes']) > 50 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this response?')">
                                    <input type="hidden" name="action" value="delete_response">
                                    <input type="hidden" name="response_id" value="<?php echo $response['id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
