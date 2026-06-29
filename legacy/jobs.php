<?php
require_once 'config/database.php';
require_once 'includes/job_manager.php';
require_once 'includes/email_notification.php';

$jobManager = new JobManager();

// Get all active jobs
$jobs = $jobManager->getAllJobs('active');

// Handle quick apply
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_apply') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $jobId = $_POST['job_id'];
    $coverLetter = sanitizeInput($_POST['cover_letter']);
    $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
    $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : null;
    $workExperience = isset($_POST['work_experience']) ? sanitizeInput($_POST['work_experience']) : null;
    
    // Handle CV upload
    $resumePath = null;
    if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if(in_array($_FILES['resume']['type'], $allowedTypes) && $_FILES['resume']['size'] <= $maxSize) {
            $uploadDir = 'uploads/';
            $fileName = uniqid() . '_' . basename($_FILES['resume']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['resume']['tmp_name'], $targetPath)) {
                $resumePath = $targetPath;
            } else {
                $error = "Gagal mengupload CV. Silakan coba lagi.";
            }
        } else {
            $error = "CV harus berupa file PDF atau Word dengan ukuran maksimal 5MB.";
        }
    }
    
    // Validasi
    if(empty($name) || empty($email) || empty($phone)) {
        $error = "Nama, email, dan telepon harus diisi";
    } elseif(!validateEmail($email)) {
        $error = "Format email tidak valid";
    } elseif($age && ($age < 18 || $age > 65)) {
        $error = "Umur harus antara 18-65 tahun";
    } else {
        $applicationManager = new ApplicationManager();
        
        $applicationId = $applicationManager->quickApply($name, $email, $phone, $jobId, $coverLetter, $age, $gender, $workExperience, $resumePath);
        if($applicationId) {
            // Get job details for notification
            $job = $jobManager->getJobById($jobId);
            
            // Send confirmation notification (log to file) with application ID
            EmailNotification::sendApplicationConfirmation($email, $name, $job['position'], $job['company'], $applicationId);
            
            // Send notification to HR (log to file)
            EmailNotification::sendHRNotification($job['position'], $job['company'], $name, $email, $phone, $coverLetter);
            
            // Generate success message with detailed confirmation including application ID
            $successMessage = EmailNotification::getConfirmationMessage($name, $job['position'], $job['company'], $email, $applicationId);
            $success = $successMessage;
        } else {
            $error = "Gagal mengirim aplikasi. Email ini mungkin sudah pernah melamar untuk posisi ini.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Kerja - PT. PUTRI KEBUN LESTARI</title>
    <link rel="icon" type="image/png" href="logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        /* Modern job card animations */
        .job-card {
            opacity: 0;
            transform: translateY(50px) scale(0.9);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .job-card.animate__animated {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        
        /* Enhanced hover effects */
        .job-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        /* Smooth transitions for all elements */
        .job-card * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Staggered animation delays */
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        .animate-delay-600 { animation-delay: 0.6s; }
        .animate-delay-700 { animation-delay: 0.7s; }
        .animate-delay-800 { animation-delay: 0.8s; }
        .animate-delay-900 { animation-delay: 0.9s; }
        
        /* Button hover effects */
        .apply-btn {
            position: relative;
            overflow: hidden;
        }
        
        .apply-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .apply-btn:hover::before {
            left: 100%;
        }
        
        /* Status badge animations */
        .status-badge {
            position: relative;
            overflow: hidden;
        }
        
        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }
        
        .status-badge:hover::before {
            left: 100%;
        }
        
        /* Card content animations */
        .job-card:hover .card-title {
            color: #3b82f6;
            transform: translateX(4px);
        }
        
        .job-card:hover .card-company {
            transform: translateX(4px);
        }
        
        .job-card:hover .card-detail {
            transform: translateX(6px);
        }
        
        /* Shine effect */
        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.8s;
            z-index: 1;
        }
        
        .job-card:hover::before {
            left: 100%;
        }
        
        /* Pulse animation for status */
        @keyframes pulse-glow {
            0%, 100% { 
                opacity: 1; 
                transform: scale(1); 
            }
            50% { 
                opacity: 0.8; 
                transform: scale(1.05); 
            }
        }
        
        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Bounce animation */
        @keyframes bounce-in {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .animate-bounce-in {
            animation: bounce-in 0.6s ease-out;
        }
    </style>
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
                    <a href="jobs.php" class="text-blue-600 bg-blue-50 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-briefcase mr-2"></i>Lowongan Kerja
                    </a>
                    <a href="check_application.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Cek Status Aplikasi
                    </a>
                    <a href="auth/login.php" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                    </a>
                    <a href="auth/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium">
                        <i class="fas fa-user-plus mr-2"></i>Daftar
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Bergabunglah dengan Tim Kami</h1>
            <p class="text-gray-600 mt-2">Temukan kesempatan karir yang tepat di PT. PUTRI KEBUN LESTARI tanpa perlu registrasi terlebih dahulu</p>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($success)): ?>
            <?php echo $success; ?>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Jobs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($jobs as $index => $job): ?>
            <?php 
            // Determine animation type based on position
            $animationType = '';
            $delayClass = '';
            
            if ($index % 3 == 0) {
                // Left column
                $animationType = 'animate__fadeInLeft';
            } elseif ($index % 3 == 1) {
                // Middle column
                $animationType = 'animate__fadeInUp';
            } else {
                // Right column
                $animationType = 'animate__fadeInRight';
            }
            
            // Add staggered delay
            $delayClass = 'animate-delay-' . (($index % 3) * 200 + 100);
            ?>
            <div class="job-card bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 hover:scale-105 group overflow-hidden relative" 
                 data-animation="<?php echo $animationType; ?>" 
                 data-delay="<?php echo $delayClass; ?>">
                
                <!-- Shine effect overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-0"></div>
                
                <div class="relative p-6 z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div class="transform group-hover:translate-x-1 transition-transform duration-300">
                            <h3 class="card-title text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-300"><?php echo htmlspecialchars($job['position']); ?></h3>
                            <p class="card-company text-sm text-gray-600"><?php echo htmlspecialchars($job['company']); ?></p>
                        </div>
                        <div class="status-badge transform group-hover:scale-110 transition-transform duration-300">
                            <?php echo getStatusBadge($job['status']); ?>
                        </div>
                    </div>
                    
                    <div class="space-y-3 mb-4">
                        <div class="card-detail flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-75">
                            <i class="fas fa-map-marker-alt mr-3 text-blue-500 group-hover:text-blue-600 transition-colors duration-300"></i>
                            <span class="group-hover:text-gray-800 transition-colors duration-300"><?php echo htmlspecialchars($job['location']); ?></span>
                        </div>
                        <div class="card-detail flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-100">
                            <i class="fas fa-briefcase mr-3 text-green-500 group-hover:text-green-600 transition-colors duration-300"></i>
                            <span class="group-hover:text-gray-800 transition-colors duration-300"><?php echo ucfirst($job['employment_type']); ?></span>
                        </div>
                        <?php if($job['salary_range']): ?>
                        <div class="card-detail flex items-center text-sm text-gray-600 transform group-hover:translate-x-2 transition-transform duration-300 delay-125">
                            <i class="fas fa-money-bill-wave mr-3 text-yellow-500 group-hover:text-yellow-600 transition-colors duration-300"></i>
                            <span class="group-hover:text-gray-800 transition-colors duration-300"><?php echo htmlspecialchars($job['salary_range']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-sm text-gray-700 mb-4 group-hover:text-gray-800 transition-colors duration-300 line-clamp-3">
                        <?php echo substr(htmlspecialchars($job['description']), 0, 120) . '...'; ?>
                    </p>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-500 group-hover:text-gray-700 transition-colors duration-300">
                            <?php echo formatDateIndonesian(date('Y-m-d', strtotime($job['created_at']))); ?>
                        </span>
                        <button onclick="openQuickApplyModal(<?php echo htmlspecialchars(json_encode($job)); ?>)" 
                                class="apply-btn bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 text-sm font-medium transform hover:scale-105 hover:shadow-lg">
                            <i class="fas fa-paper-plane mr-1 group-hover:animate-bounce"></i>Lamar Sekarang
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- About Us Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Tentang PT. PUTRI KEBUN LESTARI</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    PT. PUTRI KEBUN LESTARI adalah perusahaan restourant makanan cepat saji.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">100+ Karyawan</h3>
                    <p class="text-gray-600">Tim profesional yang berdedikasi dan berpengalaman</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-building text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">6 Tahun Pengalaman</h3>
                    <p class="text-gray-600">Berpengalaman dalam industri food and beverage sejak 2019</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">20+ Proyek</h3>
                    <p class="text-gray-600">Berhasil menyelesaikan berbagai proyek food and beverage</p>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Misi Kami</h3>
                        <p class="text-gray-600 mb-4">
                        To capture memories with food and give every person a sense of home and belonging at our locations.
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Mengembangkan food and beverage terdepan untuk masa depan
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Menciptakan lingkungan kerja yang kolaboratif dan inovatif
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-500 mr-2"></i>
                                Memberikan kesempatan pengembangan karir yang optimal
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900 mb-4">Mengapa Bergabung dengan Kami?</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                    <i class="fas fa-rocket text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Food and Beverage Terdepan</h4>
                                    <p class="text-gray-600 text-sm">At farm.girl we strive to empower women and men from all backgrounds.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                    <i class="fas fa-graduation-cap text-green-600 text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Pengembangan Karir</h4>
                                    <p class="text-gray-600 text-sm">We provide training and development opportunities for all employees.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-1">
                                    <i class="fas fa-heart text-purple-600 text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Work-Life Balance</h4>
                                    <p class="text-gray-600 text-sm">Fleksibilitas kerja dan lingkungan yang mendukung</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <img src="logo.png" alt="PT. PUTRI KEBUN LESTARI" class="w-8 h-8 object-contain">
                        <h3 class="ml-3 text-xl font-bold">PT. PUTRI KEBUN LESTARI</h3>
                    </div>
                    <p class="text-gray-300 mb-4 max-w-md">
                        PT. PUTRI KEBUN LESTARI adalah perusahaan food and beverage terdepan yang berfokus pada makanan cepat saji. 
                        Kami mencari talenta terbaik untuk bergabung dengan tim kami yang dinamis dan berdedikasi.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition duration-200">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition duration-200">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition duration-200">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition duration-200">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="jobs.php" class="text-gray-300 hover:text-white transition duration-200">Lowongan Kerja</a></li>
                        <li><a href="auth/login.php" class="text-gray-300 hover:text-white transition duration-200">Masuk HR</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition duration-200">Tentang Perusahaan</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition duration-200">Kultur Perusahaan</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition duration-200">FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Hubungi Kami</h4>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-blue-400 mr-3"></i>
                            <span class="text-gray-300">Jl. Sudirman No. 123<br>Jakarta Pusat 10270</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-phone text-blue-400 mr-3"></i>
                            <span class="text-gray-300">+62 21 1234 5678</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-400 mr-3"></i>
                            <span class="text-gray-300">info@rekruter.com</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fab fa-whatsapp text-blue-400 mr-3"></i>
                            <span class="text-gray-300">+62 812 3456 7890</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-400 text-sm mb-4 md:mb-0">
                        © 2025 PT. PUTRI KEBUN LESTARI. Semua hak dilindungi undang-undang.
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">Kebijakan Privasi</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">Syarat & Ketentuan</a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-200">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Quick Apply Modal -->
    <div id="quickApplyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 id="job-title" class="text-xl font-semibold text-gray-900"></h3>
                        <button onclick="closeQuickApplyModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="job-info" class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <!-- Job info will be populated by JavaScript -->
                    </div>
                    
                    <form method="POST" id="quick-apply-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="quick_apply">
                        <input type="hidden" name="job_id" id="apply_job_id">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan nama lengkap Anda">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="contoh@email.com">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor WhatsApp *</label>
                                <input type="tel" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="08xxxxxxxxxx">
                                <p class="text-xs text-gray-500 mt-1">Kami akan menghubungi Anda melalui WhatsApp</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Umur</label>
                                    <input type="number" name="age" min="18" max="65" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="25">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                                    <select name="gender" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Gender</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pengalaman Kerja</label>
                                <select name="work_experience" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih Pengalaman</option>
                                    <option value="Fresh Graduate">Fresh Graduate (0 tahun)</option>
                                    <option value="1-2 tahun">1-2 tahun</option>
                                    <option value="3-5 tahun">3-5 tahun</option>
                                    <option value="6-10 tahun">6-10 tahun</option>
                                    <option value="10+ tahun">10+ tahun</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload CV *</label>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX. Maksimal 5MB</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Surat Lamaran (Opsional)</label>
                                <textarea name="cover_letter" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Ceritakan mengapa Anda tertarik dengan posisi ini..."></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                    <div class="text-sm text-blue-800">
                                        <p class="font-medium">Proses Selanjutnya:</p>
                                        <ul class="mt-1 list-disc list-inside">
                                            <li>Kami akan mengirim konfirmasi ke email Anda</li>
                                            <li>Tim HR akan menghubungi Anda melalui WhatsApp</li>
                                            <li>Jika sesuai, Anda akan diundang untuk interview</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeQuickApplyModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-paper-plane mr-2"></i>Kirim Lamaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Job card animations
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing job card animations');
            const jobCards = document.querySelectorAll('.job-card');
            console.log('Found', jobCards.length, 'job cards');
            
            // Function to check if element is in viewport
            function isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
            
            // Function to animate job cards
            function animateJobCards() {
                jobCards.forEach((card, index) => {
                    if (isInViewport(card)) {
                        if (!card.classList.contains('animate__animated')) {
                            console.log('Triggering animation for card', index);
                            
                            const animationType = card.getAttribute('data-animation');
                            const delayClass = card.getAttribute('data-delay');
                            
                            // Add animate__animated class and the specific animation
                            card.classList.add('animate__animated', animationType);
                            
                            // Add delay if specified
                            if (delayClass) {
                                card.classList.add(delayClass);
                            }
                            
                            console.log('Applied animation:', animationType, 'with delay:', delayClass);
                        }
                    }
                });
            }
            
            // Enhanced hover effects for job cards
            jobCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-12px) scale(1.03)';
                    this.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                });
            });
            
            // Initial check
            animateJobCards();
            
            // Check on scroll
            window.addEventListener('scroll', function() {
                animateJobCards();
            });
            
            // Add intersection observer for better performance
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        if (!entry.target.classList.contains('animate__animated')) {
                            console.log('Intersection Observer triggered for card', index);
                            
                            const card = entry.target;
                            const animationType = card.getAttribute('data-animation');
                            const delayClass = card.getAttribute('data-delay');
                            
                            // Add animate__animated class and the specific animation
                            card.classList.add('animate__animated', animationType);
                            
                            // Add delay if specified
                            if (delayClass) {
                                card.classList.add(delayClass);
                            }
                            
                            console.log('Applied animation:', animationType, 'with delay:', delayClass);
                        }
                    }
                });
            }, observerOptions);
            
            // Observe job cards
            jobCards.forEach((card, index) => {
                console.log('Observing card', index);
                observer.observe(card);
            });
        });

        function openQuickApplyModal(job) {
            document.getElementById('job-title').textContent = job.position;
            document.getElementById('apply_job_id').value = job.id;
            
            const jobInfo = document.getElementById('job-info');
            jobInfo.innerHTML = `
                <div class="space-y-2">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-building mr-2"></i>
                        <span class="font-medium">${job.company}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <span>${job.location}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-briefcase mr-2"></i>
                        <span>${job.employment_type.charAt(0).toUpperCase() + job.employment_type.slice(1)}</span>
                    </div>
                    ${job.salary_range ? `
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-money-bill-wave mr-2"></i>
                        <span>${job.salary_range}</span>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('quickApplyModal').classList.remove('hidden');
        }

        function closeQuickApplyModal() {
            document.getElementById('quickApplyModal').classList.add('hidden');
        }
    </script>
</body>
</html>
