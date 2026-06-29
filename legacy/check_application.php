<?php
require_once 'config/database.php';
require_once 'includes/job_manager.php';
require_once 'includes/evaluation_manager.php';

$applicationManager = new ApplicationManager();
$evaluationManager = new EvaluationManager();

$application = null;
$error = null;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $applicationId = sanitizeInput($_POST['application_id']);
    
    if(empty($email) || empty($applicationId)) {
        $error = "Email dan ID Aplikasi harus diisi";
    } else {
        // Get application by ID and email
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $query = "SELECT a.*, j.position, j.company, j.location 
                     FROM applications a 
                     LEFT JOIN job_listings j ON a.job_id = j.id 
                     WHERE a.id = :app_id AND a.applicant_email = :email AND a.application_type = 'quick_apply'";
            
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':app_id', $applicationId);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!$application) {
                $error = "Aplikasi tidak ditemukan. Pastikan ID Aplikasi dan Email sudah benar.";
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
}

// Function getStatusBadge() sudah ada di config/database.php
// Mapping status untuk kompatibilitas
function getApplicationStatusBadge($status) {
    // Map status Indonesia ke status English untuk fungsi getStatusBadge()
    $statusMap = [
        'menunggu' => 'pending',
        'ditinjau' => 'reviewed', 
        'diterima' => 'accepted',
        'ditolak' => 'rejected'
    ];
    
    $mappedStatus = $statusMap[$status] ?? $status;
    return getStatusBadge($mappedStatus);
}

// Function formatDateIndonesian() sudah ada di config/database.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Aplikasi - PT. PUTRI KEBUN LESTARI</title>
    <link rel="icon" type="image/png" href="logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="logo.png" alt="PT. PUTRI KEBUN LESTARI" class="w-8 h-8 object-contain">
                        <h1 class="ml-3 text-xl font-bold text-gray-900">PT. PUTRI KEBUN LESTARI</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="jobs.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-briefcase mr-2"></i>Lowongan Kerja
                    </a>
                    <a href="check_application.php" class="text-blue-600 bg-blue-50 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Cek Status Aplikasi
                    </a>
                    <a href="auth/login.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Cek Status Aplikasi</h1>
            <p class="text-gray-600">Masukkan email dan ID aplikasi untuk melihat status lamaran Anda</p>
        </div>

        <!-- Search Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="email@example.com">
                    </div>
                    <div>
                        <label for="application_id" class="block text-sm font-medium text-gray-700 mb-2">ID Aplikasi</label>
                        <input type="text" id="application_id" name="application_id" required 
                               value="<?php echo isset($_POST['application_id']) ? htmlspecialchars($_POST['application_id']) : ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Contoh: 123">
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-search mr-2"></i>Cek Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Error Message -->
        <?php if($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Application Details -->
        <?php if($application): ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Detail Aplikasi</h2>
                <p class="text-gray-600">ID Aplikasi: #<?php echo $application['id']; ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pelamar</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($application['applicant_name']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($application['applicant_email']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Telepon</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($application['applicant_phone']); ?></p>
                        </div>
                        <?php if($application['applicant_age']): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Umur</label>
                            <p class="text-gray-900"><?php echo $application['applicant_age']; ?> tahun</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Posisi</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Posisi</label>
                            <p class="text-gray-900 font-semibold"><?php echo htmlspecialchars($application['position']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Perusahaan</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($application['company']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Lokasi</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($application['location']); ?></p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Melamar</label>
                            <p class="text-gray-900"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($application['applied_at']))); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Status Aplikasi</h3>
                    <?php echo getApplicationStatusBadge($application['status']); ?>
                </div>

                <!-- Status Timeline -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-900">Aplikasi Diterima</p>
                            <p class="text-sm text-gray-500"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($application['applied_at']))); ?></p>
                        </div>
                    </div>
                    
                    <?php if($application['status'] != 'menunggu'): ?>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-900">Sedang Diproses</p>
                            <p class="text-sm text-gray-500">Aplikasi sedang ditinjau oleh tim HR</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($application['status'] == 'diterima' || $application['status'] == 'ditolak'): ?>
                    <div class="flex items-center">
                        <div class="w-3 h-3 <?php echo $application['status'] == 'diterima' ? 'bg-green-500' : 'bg-red-500'; ?> rounded-full mr-3"></div>
                        <div>
                            <p class="font-medium text-gray-900">
                                <?php echo $application['status'] == 'diterima' ? 'Aplikasi Diterima' : 'Aplikasi Ditolak'; ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                <?php echo $application['status'] == 'diterima' ? 'Selamat! Anda akan dihubungi untuk tahap selanjutnya.' : 'Terima kasih atas minat Anda. Tetap semangat untuk kesempatan lainnya.'; ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Evaluation Results (if available) -->
            <?php
            // Check if there are evaluation results
            try {
                $rankings = $evaluationManager->getJobRankings($application['job_id']);
                $userRanking = null;
                foreach($rankings as $ranking) {
                    if($ranking['application_id'] == $application['id']) {
                        $userRanking = $ranking;
                        break;
                    }
                }
                
                if($userRanking && $application['status'] != 'menunggu'):
            ?>
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Hasil Penilaian</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Skor SAW</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($userRanking['saw_score'] * 100, 1); ?>%</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Ranking</p>
                        <p class="text-2xl font-bold text-green-600">#<?php echo $userRanking['rank_position']; ?></p>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-600">Total Pelamar</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo count($rankings); ?></p>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-2"></i>
                        Penilaian menggunakan metode SAW (Simple Additive Weighting) berdasarkan kriteria yang telah ditetapkan perusahaan.
                    </p>
                </div>
            </div>
            <?php 
                endif;
            } catch(Exception $e) {
                // Ignore evaluation errors for now
            }
            ?>

            <!-- Contact Information -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Butuh Bantuan?</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <i class="fas fa-phone text-blue-600 text-2xl mb-2"></i>
                        <p class="font-medium">Telepon</p>
                        <p class="text-sm text-gray-600">+62 21 1234 5678</p>
                    </div>
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <i class="fas fa-envelope text-green-600 text-2xl mb-2"></i>
                        <p class="font-medium">Email</p>
                        <p class="text-sm text-gray-600">info@PT. PUTRI KEBUN LESTARI.com</p>
                    </div>
                    <div class="text-center p-4 border border-gray-200 rounded-lg">
                        <i class="fab fa-whatsapp text-green-600 text-2xl mb-2"></i>
                        <p class="font-medium">WhatsApp</p>
                        <p class="text-sm text-gray-600">+62 812 3456 7890</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Help Section -->
        <?php if(!$application): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">
                <i class="fas fa-question-circle mr-2"></i>Cara Mendapatkan ID Aplikasi
            </h3>
            <div class="space-y-3 text-blue-800">
                <p>• ID Aplikasi dikirimkan ke email Anda setelah berhasil melamar</p>
                <p>• Cek folder inbox dan spam di email Anda</p>
                <p>• ID Aplikasi berupa angka (contoh: 123, 456, dll.)</p>
                <p>• Jika tidak menemukan email konfirmasi, hubungi kami di kontak di bawah</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <div class="flex items-center justify-center mb-4">
                    <img src="logo.png" alt="PT. PUTRI KEBUN LESTARI" class="w-8 h-8 object-contain mr-3">
                    <h3 class="text-xl font-bold">PT. PUTRI KEBUN LESTARI</h3>
                </div>
                <p class="text-gray-400 mb-4">Siap merekrut karyawan terbaik</p>
                <div class="flex justify-center space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
