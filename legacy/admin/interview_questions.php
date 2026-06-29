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

// Handle question creation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_question') {
    $questionText = sanitizeInput($_POST['question_text']);
    $questionType = $_POST['question_type'];
    $difficultyLevel = $_POST['difficulty_level'];
    $jobPosition = sanitizeInput($_POST['job_position']);
    
    // Create question directly
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO interview_questions (question_text, question_type, difficulty_level, job_position, created_by) VALUES (:question_text, :question_type, :difficulty_level, :job_position, :created_by)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_text', $questionText);
        $stmt->bindParam(':question_type', $questionType);
        $stmt->bindParam(':difficulty_level', $difficultyLevel);
        $stmt->bindParam(':job_position', $jobPosition);
        $stmt->bindParam(':created_by', $_SESSION['user_id']);
        
        if($stmt->execute()) {
            $success = "Pertanyaan interview berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan pertanyaan interview.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle question update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_question') {
    $questionId = $_POST['question_id'];
    $questionText = sanitizeInput($_POST['question_text']);
    $questionType = $_POST['question_type'];
    $difficultyLevel = $_POST['difficulty_level'];
    $jobPosition = sanitizeInput($_POST['job_position']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Update question directly
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE interview_questions SET 
                    question_text = :question_text,
                    question_type = :question_type,
                    difficulty_level = :difficulty_level,
                    job_position = :job_position,
                    is_active = :is_active
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':question_text', $questionText);
        $stmt->bindParam(':question_type', $questionType);
        $stmt->bindParam(':difficulty_level', $difficultyLevel);
        $stmt->bindParam(':job_position', $jobPosition);
        $stmt->bindParam(':is_active', $isActive);
        $stmt->bindParam(':id', $questionId);
        
        if($stmt->execute()) {
            $success = "Pertanyaan interview berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui pertanyaan interview.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle question deletion
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_question') {
    $questionId = $_POST['question_id'];
    
    // Delete question directly
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "DELETE FROM interview_questions WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $questionId);
        
        if($stmt->execute()) {
            $success = "Pertanyaan interview berhasil dihapus!";
        } else {
            $error = "Gagal menghapus pertanyaan interview.";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all questions
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get interview questions
    $query = "SELECT iq.*, u.name as created_by_name 
              FROM interview_questions iq 
              LEFT JOIN users u ON iq.created_by = u.id 
              ORDER BY iq.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get job positions
    $query = "SELECT position FROM job_listings WHERE status = 'active' ORDER BY position";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $jobPositions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $questions = [];
    $jobPositions = [];
}

function getTypeBadge($type) {
    $badges = [
        'technical' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Teknis</span>',
        'behavioral' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Behavioral</span>',
        'situational' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Situational</span>',
        'cultural' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Cultural</span>',
        'general' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">General</span>'
    ];
    return $badges[$type] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
}

function getDifficultyBadge($difficulty) {
    $badges = [
        'easy' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Mudah</span>',
        'medium' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Sedang</span>',
        'hard' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sulit</span>'
    ];
    return $badges[$difficulty] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pertanyaan Interview - Admin</title>
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
                            <h1 class="text-3xl font-bold text-gray-900">Pertanyaan Interview</h1>
                            <p class="text-gray-600 mt-2">Kelola bank pertanyaan untuk interview</p>
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

                    <!-- Create Question Button -->
                    <div class="mb-6">
                        <button onclick="openCreateModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Tambah Pertanyaan
                        </button>
                    </div>

                    <!-- Questions Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Pertanyaan Interview</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertanyaan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kesulitan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if(empty($questions)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            <i class="fas fa-question-circle text-4xl mb-2"></i>
                                            <p>Belum ada pertanyaan interview</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($questions as $question): ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(substr($question['question_text'], 0, 100)) . (strlen($question['question_text']) > 100 ? '...' : ''); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getTypeBadge($question['question_type']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getDifficultyBadge($question['difficulty_level']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $question['job_position'] ? htmlspecialchars($question['job_position']) : 'General'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($question['is_active']): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                            <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($question)); ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button onclick="deleteQuestion(<?php echo $question['id']; ?>)" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
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

    <!-- Create/Edit Question Modal -->
    <div id="questionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 id="modal-title" class="text-xl font-semibold text-gray-900">Tambah Pertanyaan Interview</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form method="POST" id="question-form">
                        <input type="hidden" name="action" id="form-action" value="create_question">
                        <input type="hidden" name="question_id" id="question_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan *</label>
                                <textarea name="question_text" id="question_text" required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan pertanyaan interview..."></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pertanyaan *</label>
                                    <select name="question_type" id="question_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Tipe</option>
                                        <option value="technical">Teknis</option>
                                        <option value="behavioral">Behavioral</option>
                                        <option value="situational">Situational</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tingkat Kesulitan *</label>
                                    <select name="difficulty_level" id="difficulty_level" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Kesulitan</option>
                                        <option value="easy">Mudah</option>
                                        <option value="medium">Sedang</option>
                                        <option value="hard">Sulit</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Posisi Kerja (Opsional)</label>
                                <select name="job_position" id="job_position" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih Posisi (kosongkan untuk general)</option>
                                    <?php foreach($jobPositions as $job): ?>
                                    <option value="<?php echo htmlspecialchars($job['position']); ?>">
                                        <?php echo htmlspecialchars($job['position']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="active-field" class="hidden">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Hapus</h3>
                            <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus pertanyaan ini?</p>
                        </div>
                    </div>
                    
                    <form method="POST" id="delete-form">
                        <input type="hidden" name="action" value="delete_question">
                        <input type="hidden" name="question_id" id="delete_question_id">
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                                <i class="fas fa-trash mr-2"></i>Hapus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('modal-title').textContent = 'Tambah Pertanyaan Interview';
            document.getElementById('form-action').value = 'create_question';
            document.getElementById('question-form').reset();
            document.getElementById('active-field').classList.add('hidden');
            document.getElementById('questionModal').classList.remove('hidden');
        }

        function openEditModal(question) {
            document.getElementById('modal-title').textContent = 'Edit Pertanyaan Interview';
            document.getElementById('form-action').value = 'update_question';
            document.getElementById('question_id').value = question.id;
            document.getElementById('question_text').value = question.question_text;
            document.getElementById('question_type').value = question.question_type;
            document.getElementById('difficulty_level').value = question.difficulty_level;
            document.getElementById('job_position').value = question.job_position || '';
            document.getElementById('is_active').checked = question.is_active == 1;
            document.getElementById('active-field').classList.remove('hidden');
            document.getElementById('questionModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('questionModal').classList.add('hidden');
        }

        function deleteQuestion(questionId) {
            document.getElementById('delete_question_id').value = questionId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
    </div>
</body>
</html>
