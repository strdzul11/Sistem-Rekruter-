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

// Get interview ID from URL
$interviewId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$interviewId) {
    header('Location: dashboard.php');
    exit();
}

// Get interview details
$interview = $interviewManager->getInterviewDetails($interviewId);
if (!$interview) {
    header('Location: dashboard.php');
    exit();
}

// Verify that this interview belongs to the current user
$currentUserId = $_SESSION['user_id'];
if ($interview['applicant_id'] != $currentUserId) {
    header('Location: dashboard.php');
    exit();
}

// Get interview responses
$responses = $interviewManager->getInterviewResponses($interviewId);
$responseStats = $interviewManager->getResponseStatistics($interviewId);

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

// Helper function for overall performance
function getOverallPerformance($stats) {
    if (empty($stats)) return 'No data available';
    
    $totalResponses = array_sum(array_column($stats, 'total_responses'));
    $avgScore = array_sum(array_column($stats, 'average_score')) / count($stats);
    
    if ($avgScore >= 4) return 'Excellent Performance';
    if ($avgScore >= 3.5) return 'Very Good Performance';
    if ($avgScore >= 3) return 'Good Performance';
    if ($avgScore >= 2.5) return 'Fair Performance';
    return 'Needs Improvement';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Responses - Pelamar</title>
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
                <li><a href="dashboard.php" class="text-gray-500 hover:text-gray-700">Dashboard</a></li>
                <li><i class="fas fa-chevron-right text-gray-400"></i></li>
                <li><span class="text-gray-900 font-medium">Interview Responses</span></li>
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
                        <label class="text-sm font-medium text-gray-500">Position</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($interview['position']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Company</label>
                        <p class="text-gray-900"><?php echo htmlspecialchars($interview['company']); ?></p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Interview Date</label>
                        <p class="text-gray-900"><?php echo date('d M Y H:i', strtotime($interview['interview_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Performance -->
        <?php if (!empty($responseStats)): ?>
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Overall Performance</h3>
            </div>
            <div class="px-6 py-4">
                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-lg font-medium <?php 
                        $overall = getOverallPerformance($responseStats);
                        if (strpos($overall, 'Excellent') !== false) echo 'bg-green-100 text-green-800';
                        elseif (strpos($overall, 'Very Good') !== false) echo 'bg-blue-100 text-blue-800';
                        elseif (strpos($overall, 'Good') !== false) echo 'bg-yellow-100 text-yellow-800';
                        elseif (strpos($overall, 'Fair') !== false) echo 'bg-orange-100 text-orange-800';
                        else echo 'bg-red-100 text-red-800';
                    ?>">
                        <i class="fas fa-chart-line mr-2"></i>
                        <?php echo $overall; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Response Statistics -->
        <?php if (!empty($responseStats)): ?>
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Performance by Category</h3>
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
                                <p class="text-lg font-semibold text-orange-600"><?php echo number_format($stat['average_score'], 1); ?></p>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between text-xs text-gray-500">
                            <span>Good: <?php echo $stat['good_responses']; ?></span>
                            <span>Poor: <?php echo $stat['poor_responses']; ?></span>
                        </div>
                        <div class="mt-2">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-orange-600 h-2 rounded-full" style="width: <?php echo ($stat['average_score'] / 5) * 100; ?>%"></div>
                            </div>
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
                <h3 class="text-lg font-semibold text-gray-900">Your Interview Responses</h3>
                <p class="text-sm text-gray-500 mt-1">Detailed feedback from your interview</p>
            </div>
            <div class="overflow-x-auto">
                <?php if (empty($responses)): ?>
                <div class="px-6 py-8 text-center">
                    <i class="fas fa-comments text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No responses recorded yet</p>
                    <p class="text-sm text-gray-400 mt-2">Responses will appear here after the interview is completed</p>
                </div>
                <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($responses as $index => $response): ?>
                    <div class="px-6 py-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <span class="text-orange-600 font-medium text-sm"><?php echo $index + 1; ?></span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <?php echo getQuestionTypeBadge($response['question_type']); ?>
                                        <?php echo getScoreBadge($response['score']); ?>
                                    </div>
                                    <span class="text-xs text-gray-500">
                                        <?php echo date('M d, Y H:i', strtotime($response['created_at'])); ?>
                                    </span>
                                </div>
                                
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Question</h4>
                                <p class="text-sm text-gray-700 mb-4 bg-gray-50 p-3 rounded-md">
                                    <?php echo htmlspecialchars($response['question_text']); ?>
                                </p>
                                
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Your Response</h4>
                                <p class="text-sm text-gray-700 mb-4">
                                    <?php echo nl2br(htmlspecialchars($response['response_text'])); ?>
                                </p>
                                
                                <?php if (!empty($response['notes'])): ?>
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Interviewer Notes</h4>
                                <p class="text-sm text-gray-600 bg-blue-50 p-3 rounded-md">
                                    <?php echo nl2br(htmlspecialchars($response['notes'])); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-between">
            <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
            
            <?php if (!empty($responses)): ?>
            <a href="evaluation_results.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700">
                <i class="fas fa-chart-bar mr-2"></i>
                View Evaluation Results
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
