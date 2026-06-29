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

// Generate meeting link if not exists
if (!$interview['meeting_link']) {
    $meetingLink = $interviewManager->generateMeetingLink('video');
    $interviewManager->updateInterviewStatus($interviewId, 'confirmed', $meetingLink);
    $interview['meeting_link'] = $meetingLink;
}

// Get interview questions from database
$db = new Database();
$questions = [];

try {
    $stmt = $db->getConnection()->prepare("
        SELECT question_text, question_type, difficulty_level, job_position 
        FROM interview_questions 
        WHERE is_active = 1 
        ORDER BY question_type, difficulty_level, id
    ");
    $stmt->execute();
    $dbQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group questions by type
    foreach ($dbQuestions as $q) {
        $type = strtolower($q['question_type']);
        if (!isset($questions[$type])) {
            $questions[$type] = [];
        }
        $questions[$type][] = $q['question_text'];
    }
} catch (PDOException $e) {
    error_log("Error loading interview questions: " . $e->getMessage());
    // Fallback to hardcoded questions if database fails
    $questions = [
        'general' => [
            "Ceritakan tentang diri Anda dan pengalaman kerja Anda",
            "Mengapa Anda tertarik dengan posisi ini?",
            "Apa yang Anda ketahui tentang perusahaan kami?"
        ],
        'technical' => [
            "Jelaskan teknologi yang Anda kuasai dengan baik",
            "Bagaimana Anda mengatasi bug atau masalah teknis?"
        ],
        'behavioral' => [
            "Ceritakan tentang situasi sulit yang pernah Anda hadapi di tempat kerja",
            "Bagaimana Anda menangani konflik dengan rekan kerja?"
        ],
        'situational' => [
            "Bagaimana Anda menangani situasi di mana Anda tidak setuju dengan atasan?",
            "Ceritakan tentang proyek yang gagal dan bagaimana Anda menanganinya?"
        ]
    ];
}

$user = $auth->getUserById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Interview - HRD</title>
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
                                <h1 class="text-3xl font-bold text-gray-900">Video Interview</h1>
                                <p class="text-gray-600 mt-2">Interview dengan <?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="flex space-x-3">
                                    <a href="interview_feedback.php?id=<?php echo $interviewId; ?>" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                        <i class="fas fa-clipboard-list mr-2"></i>Feedback
                                    </a>
                                    <a href="interviews.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                                    </a>
                                </div>
                                
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

                    <!-- Interview Information -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Interview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Pelamar</p>
                                <p class="font-medium"><?php echo htmlspecialchars($interview['applicant_name']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($interview['applicant_email']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Posisi</p>
                                <p class="font-medium"><?php echo htmlspecialchars($interview['position']); ?></p>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($interview['company']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal & Waktu</p>
                                <p class="font-medium"><?php echo date('d M Y', strtotime($interview['interview_date'])); ?></p>
                                <p class="text-sm text-gray-500"><?php echo date('H:i', strtotime($interview['interview_date'])); ?> (<?php echo $interview['timezone']; ?>)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Video Conference Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Video Conference -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-lg shadow">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Video Conference</h3>
                                    
                                    <!-- Meeting Link -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Meeting Link</label>
                                        <div class="flex">
                                            <input type="text" id="meetingLink" value="<?php echo htmlspecialchars($interview['meeting_link']); ?>" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-l-lg bg-gray-50">
                                            <button onclick="copyMeetingLink()" class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition duration-200">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Bagikan link ini dengan kandidat untuk bergabung dalam video call</p>
                                    </div>
                                    
                                    <!-- Video Container -->
                                    <div class="bg-gray-900 rounded-lg p-4 mb-4">
                                        <div class="aspect-video bg-gray-800 rounded-lg flex items-center justify-center">
                                            <div class="text-center text-white">
                                                <i class="fas fa-video text-4xl mb-4"></i>
                                                <p class="text-lg font-medium">Video Conference</p>
                                                <p class="text-sm text-gray-300">Klik link meeting untuk bergabung</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Meeting Controls -->
                                    <div class="flex justify-center space-x-4">
                                        <button onclick="joinMeeting()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                            <i class="fas fa-video mr-2"></i>Join Meeting
                                        </button>
                                        <button onclick="shareScreen()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                            <i class="fas fa-desktop mr-2"></i>Share Screen
                                        </button>
                                        <button onclick="toggleMute()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                                            <i class="fas fa-microphone mr-2"></i>Mute
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Interview Tools -->
                        <div class="space-y-6">
                            <!-- Interview Questions -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Interview Questions</h3>
                                <div class="space-y-3">
                                    <button onclick="showQuestion('general')" class="w-full text-left p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                                        General Questions
                                    </button>
                                    <button onclick="showQuestion('technical')" class="w-full text-left p-3 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                                        <i class="fas fa-code text-green-600 mr-2"></i>
                                        Technical Questions
                                    </button>
                                    <button onclick="showQuestion('behavioral')" class="w-full text-left p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                                        <i class="fas fa-users text-purple-600 mr-2"></i>
                                        Behavioral Questions
                                    </button>
                                    <button onclick="showQuestion('situational')" class="w-full text-left p-3 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200">
                                        <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                                        Situational Questions
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Interview Notes -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Interview Notes</h3>
                                <textarea id="interviewNotes" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Catat poin-poin penting selama interview..."></textarea>
                                <button onclick="saveNotes()" class="mt-3 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Notes
                                </button>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="bg-white rounded-lg shadow p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                <div class="space-y-2">
                                    <button onclick="startRecording()" class="w-full text-left p-3 bg-red-50 rounded-lg hover:bg-red-100 transition duration-200">
                                        <i class="fas fa-record-vinyl text-red-600 mr-2"></i>
                                        Start Recording
                                    </button>
                                    <button onclick="takeScreenshot()" class="w-full text-left p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                        <i class="fas fa-camera text-gray-600 mr-2"></i>
                                        Take Screenshot
                                    </button>
                                    <button onclick="endInterview()" class="w-full text-left p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition duration-200">
                                        <i class="fas fa-stop text-orange-600 mr-2"></i>
                                        End Interview
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Modal -->
    <div id="questionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="questionTitle" class="text-lg font-semibold text-gray-900">Interview Questions</h3>
                        <button onclick="closeQuestionModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="questionContent" class="space-y-4">
                        <!-- Questions will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Questions loaded from database
        const questions = <?php echo json_encode($questions); ?>;

        function copyMeetingLink() {
            const meetingLink = document.getElementById('meetingLink');
            meetingLink.select();
            document.execCommand('copy');
            
            // Show success message
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.classList.add('bg-green-600');
            button.classList.remove('bg-blue-600');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600');
                button.classList.add('bg-blue-600');
            }, 2000);
        }

        function joinMeeting() {
            const meetingLink = document.getElementById('meetingLink').value;
            if (meetingLink) {
                window.open(meetingLink, '_blank');
            }
        }

        function shareScreen() {
            alert('Fitur share screen akan diimplementasikan dengan WebRTC API');
        }

        function toggleMute() {
            alert('Fitur mute akan diimplementasikan dengan WebRTC API');
        }

        function showQuestion(category) {
            const modal = document.getElementById('questionModal');
            const title = document.getElementById('questionTitle');
            const content = document.getElementById('questionContent');
            
            title.textContent = category.charAt(0).toUpperCase() + category.slice(1) + ' Questions';
            
            content.innerHTML = questions[category].map((question, index) => `
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <span class="flex-shrink-0 w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">
                            ${index + 1}
                        </span>
                        <div class="flex-1">
                            <p class="text-gray-900">${question}</p>
                            <div class="mt-2 flex space-x-2">
                                <button onclick="askQuestion('${question}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-microphone mr-1"></i>Ask
                                </button>
                                <button onclick="markAsked('${question}')" class="text-green-600 hover:text-green-800 text-sm">
                                    <i class="fas fa-check mr-1"></i>Asked
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            modal.classList.remove('hidden');
        }

        function closeQuestionModal() {
            document.getElementById('questionModal').classList.add('hidden');
        }

        function askQuestion(question) {
            // In a real implementation, this would use WebRTC to speak the question
            alert('Asking: ' + question);
        }

        function markAsked(question) {
            // Mark question as asked
            alert('Question marked as asked: ' + question);
        }

        function saveNotes() {
            const notes = document.getElementById('interviewNotes').value;
            if (notes.trim()) {
                // In a real implementation, save to database
                alert('Notes saved successfully!');
            } else {
                alert('Please enter some notes first.');
            }
        }

        function startRecording() {
            alert('Recording started! (This would integrate with WebRTC recording API)');
        }

        function takeScreenshot() {
            alert('Screenshot taken! (This would use Canvas API to capture video frame)');
        }

        function endInterview() {
            if (confirm('Are you sure you want to end the interview?')) {
                // Update interview status to completed
                window.location.href = 'interview_feedback.php?id=<?php echo $interviewId; ?>';
            }
        }

        // Auto-save notes every 30 seconds
        setInterval(() => {
            const notes = document.getElementById('interviewNotes').value;
            if (notes.trim()) {
                // Auto-save to localStorage
                localStorage.setItem('interview_notes_<?php echo $interviewId; ?>', notes);
            }
        }, 30000);

        // Load saved notes on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedNotes = localStorage.getItem('interview_notes_<?php echo $interviewId; ?>');
            if (savedNotes) {
                document.getElementById('interviewNotes').value = savedNotes;
            }
        });
    </script>
</body>
</html>
