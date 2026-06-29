<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Cek apakah user sudah login dan role applicant
if(!isLoggedIn() || getUserRole() !== 'applicant') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = sanitizeInput($_POST['name']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $age = isset($_POST['age']) ? (int)$_POST['age'] : null;
        $gender = sanitizeInput($_POST['gender']);
        $workExperience = sanitizeInput($_POST['work_experience']);
        
        // Handle resume upload
        $resumePath = null;
        if(isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            
            if(in_array($fileExtension, $allowedExtensions)) {
                $fileName = uniqid() . '_' . $_FILES['resume']['name'];
                $filePath = $uploadDir . $fileName;
                
                if(move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                    $resumePath = $fileName;
                }
            }
        }
        
        // Validate required fields
        if(empty($name) || empty($phone) || empty($address) || empty($age) || empty($gender) || empty($workExperience)) {
            $error = "Semua field yang bertanda * harus diisi.";
        } else {
            if($auth->updateProfileWithDetails($_SESSION['user_id'], $name, $phone, $address, $age, $gender, $workExperience, $resumePath)) {
                $success = "Profil berhasil diupdate";
                // Refresh user data
                $user = $auth->getUserById($_SESSION['user_id']);
            } else {
                $error = "Gagal mengupdate profil";
            }
        }
    }
    
    if(isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if($newPassword !== $confirmPassword) {
            $error = "Password baru dan konfirmasi password tidak cocok";
        } elseif($auth->changePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
            $success = "Password berhasil diubah";
        } else {
            $error = "Password lama salah atau gagal mengubah password";
        }
    }
}

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PT. PUTRI KEBUN LESTARI</title>
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
                <a href="profile.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-user mr-3"></i>
                    Profil
                </a>
                <a href="evaluation_results.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-star mr-3"></i>
                    Hasil Penilaian
                </a>
            </nav>
            
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Profil Saya</h1>
                    <p class="text-gray-600 mt-2">Kelola informasi profil dan akun Anda</p>
                </div>
                
                <!-- User Profile Section -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                            <p class="text-xs text-gray-500">Pelamar</p>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="flex items-center text-gray-600 hover:text-gray-900 text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if(isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Info -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Profil</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
                                    <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon *</label>
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="08xxxxxxxxxx">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Umur *</label>
                                    <input type="number" name="age" value="<?php echo htmlspecialchars($user['age'] ?: ''); ?>" required min="18" max="65" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="25">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin *</label>
                                    <select name="gender" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki" <?php echo ($user['gender'] ?? '') === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="Perempuan" <?php echo ($user['gender'] ?? '') === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <input type="text" value="Pelamar" disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pengalaman Kerja *</label>
                                <select name="work_experience" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Pilih Pengalaman Kerja</option>
                                    <option value="Fresh Graduate" <?php echo ($user['work_experience'] ?? '') === 'Fresh Graduate' ? 'selected' : ''; ?>>Fresh Graduate (0 tahun)</option>
                                    <option value="1-2 tahun" <?php echo ($user['work_experience'] ?? '') === '1-2 tahun' ? 'selected' : ''; ?>>1-2 tahun</option>
                                    <option value="3-5 tahun" <?php echo ($user['work_experience'] ?? '') === '3-5 tahun' ? 'selected' : ''; ?>>3-5 tahun</option>
                                    <option value="6-10 tahun" <?php echo ($user['work_experience'] ?? '') === '6-10 tahun' ? 'selected' : ''; ?>>6-10 tahun</option>
                                    <option value="10+ tahun" <?php echo ($user['work_experience'] ?? '') === '10+ tahun' ? 'selected' : ''; ?>>10+ tahun</option>
                                </select>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap *</label>
                                <textarea name="address" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Masukkan alamat lengkap Anda"><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload CV/Resume</label>
                                <input type="file" name="resume" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX. Maksimal 5MB</p>
                                <?php if(!empty($user['resume_path'])): ?>
                                <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-lg">
                                    <p class="text-sm text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        CV sudah diupload: <?php echo htmlspecialchars($user['resume_path']); ?>
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex">
                                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                                        <div class="text-sm text-blue-800">
                                            <p class="font-medium">Informasi Penting:</p>
                                            <p class="mt-1">Pastikan informasi profil Anda lengkap dan akurat. Data ini akan digunakan untuk proses aplikasi kerja.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-lg shadow mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Ubah Password</h3>
                    </div>
                    <div class="p-6">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Lama</label>
                                    <input type="password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                    <input type="password" name="new_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                                    <i class="fas fa-key mr-2"></i>Ubah Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Account Info -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Akun</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-blue-600 text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Pelamar</span>
                        </div>
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-calendar-alt mr-3"></i>
                                <span>Bergabung: <?php echo formatDateIndonesian(date('Y-m-d', strtotime($user['created_at']))); ?></span>
                            </div>
                            <?php if($user['phone']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone mr-3"></i>
                                <span><?php echo htmlspecialchars($user['phone']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Aksi Cepat</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="dashboard.php" class="flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-briefcase mr-3"></i>
                                Lihat Lowongan Kerja
                            </a>
                            <a href="dashboard.php#applications" class="flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                                <i class="fas fa-file-alt mr-3"></i>
                                Lihat Aplikasi Saya
                            </a>
                            <a href="../auth/logout.php" class="flex items-center text-red-600 hover:text-red-700 text-sm font-medium">
                                <i class="fas fa-sign-out-alt mr-3"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
