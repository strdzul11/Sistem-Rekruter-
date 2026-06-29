<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/job_manager.php';

// Cek apakah user sudah login dan role HRD
if(!isLoggedIn() || getUserRole() !== 'hrd') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$applicationManager = new ApplicationManager();

// Handle actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'update_status':
                $id = $_POST['application_id'];
                $status = sanitizeInput($_POST['status']);
                
                if($applicationManager->updateApplicationStatus($id, $status)) {
                    $success = "Status aplikasi berhasil diupdate";
                } else {
                    $error = "Gagal mengupdate status aplikasi";
                }
                break;
        }
    }
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$jobFilter = $_GET['job_id'] ?? '';

// Get all applications with filters
$applications = $applicationManager->getAllApplications($statusFilter ?: null, $jobFilter ?: null);

// Get all jobs for filter
$jobManager = new JobManager();
$jobs = $jobManager->getAllJobs();

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Aplikasi - HRD</title>
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
                <a href="applications.php" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
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
                <a href="interviews.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-video mr-3"></i>
                    Manajemen Interview
                </a>
                
                <div class="px-6 py-2 mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lainnya</p>
                </div>
                <a href="jobs.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-briefcase mr-3"></i>
                    Lowongan Kerja
                </a>
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
                                <h1 class="text-3xl font-bold text-gray-900">Manajemen Aplikasi</h1>
                                <p class="text-gray-600 mt-2">Kelola dan review aplikasi pelamar</p>
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

                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Aplikasi</h3>
                        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="reviewed" <?php echo $statusFilter === 'reviewed' ? 'selected' : ''; ?>>Ditinjau</option>
                                    <option value="accepted" <?php echo $statusFilter === 'accepted' ? 'selected' : ''; ?>>Diterima</option>
                                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Ditolak</option>
                                    <option value="interview_scheduled" <?php echo $statusFilter === 'interview_scheduled' ? 'selected' : ''; ?>>Wawancara</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lowongan Kerja</label>
                                <select name="job_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Semua Lowongan</option>
                                    <?php foreach($jobs as $job): ?>
                                    <option value="<?php echo $job['id']; ?>" <?php echo $jobFilter == $job['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($job['position']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-filter mr-2"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Applications Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Aplikasi</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelamar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lowongan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perusahaan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Apply</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($applications as $app): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($app['applicant_email']); ?></div>
                                                    <?php if($app['applicant_age'] || $app['applicant_gender'] || $app['work_experience']): ?>
                                                    <div class="text-xs text-gray-400 mt-1">
                                                        <?php if($app['applicant_age']): ?>
                                                            Umur: <?php echo $app['applicant_age']; ?> tahun
                                                        <?php endif; ?>
                                                        <?php if($app['applicant_gender']): ?>
                                                            <?php echo $app['applicant_age'] ? ' | ' : ''; ?><?php echo $app['applicant_gender']; ?>
                                                        <?php endif; ?>
                                                        <?php if($app['work_experience']): ?>
                                                            <?php echo ($app['applicant_age'] || $app['applicant_gender']) ? ' | ' : ''; ?><?php echo $app['work_experience']; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($app['position']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($app['company']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $typeBadges = [
                                                'registered' => '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Registered</span>',
                                                'quick_apply' => '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Quick Apply</span>'
                                            ];
                                            echo $typeBadges[$app['application_type']] ?? '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Unknown</span>';
                                            ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo getStatusBadge($app['status']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo formatDateIndonesian(date('Y-m-d', strtotime($app['applied_at']))); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openStatusModal(<?php echo $app['id']; ?>, '<?php echo $app['status']; ?>')" class="text-blue-600 hover:text-blue-900 mr-3" title="Update Status">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="viewApplication(<?php echo $app['id']; ?>)" class="text-green-600 hover:text-green-900" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
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

    <!-- Update Status Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Status Aplikasi</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="application_id" id="status_application_id">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status Baru</label>
                                <select name="status" id="status_select" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="pending">Menunggu</option>
                                    <option value="reviewed">Ditinjau</option>
                                    <option value="accepted">Diterima</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="interview_scheduled">Wawancara</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeStatusModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Detail Modal -->
    <div id="applicationDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Detail Aplikasi</h3>
                        <button onclick="closeApplicationDetailModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div id="application-detail-content">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openStatusModal(applicationId, currentStatus) {
            document.getElementById('status_application_id').value = applicationId;
            document.getElementById('status_select').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        function viewApplication(applicationId) {
            // Show loading state
            const content = document.getElementById('application-detail-content');
            content.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2 text-gray-600">Memuat detail aplikasi...</p></div>';
            document.getElementById('applicationDetailModal').classList.remove('hidden');
            
            // Fetch application details via AJAX
            fetch('get_application_detail.php?id=' + applicationId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if(data.success) {
                        populateApplicationDetail(data.application);
                    } else {
                        content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">' + (data.message || 'Gagal memuat detail aplikasi') + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    content.innerHTML = '<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-2xl text-red-600"></i><p class="mt-2 text-gray-600">Terjadi kesalahan: ' + error.message + '</p></div>';
                });
        }

        function populateApplicationDetail(app) {
            const content = document.getElementById('application-detail-content');
            
            const statusBadges = {
                'pending': '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Menunggu</span>',
                'reviewed': '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Ditinjau</span>',
                'accepted': '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Diterima</span>',
                'rejected': '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Ditolak</span>',
                'interview_scheduled': '<span class="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">Wawancara</span>'
            };
            
            const typeBadges = {
                'registered': '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Registered</span>',
                'quick_apply': '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Quick Apply</span>'
            };
            
            content.innerHTML = `
                <div class="space-y-6">
                    <!-- Job Information -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Informasi Lowongan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Posisi:</span>
                                <p class="text-sm text-gray-900">${app.position}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Perusahaan:</span>
                                <p class="text-sm text-gray-900">${app.company}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Lokasi:</span>
                                <p class="text-sm text-gray-900">${app.location}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Tanggal Apply:</span>
                                <p class="text-sm text-gray-900">${new Date(app.applied_at).toLocaleDateString('id-ID')}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Applicant Information -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Informasi Pelamar</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Nama:</span>
                                <p class="text-sm text-gray-900">${app.applicant_name}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Email:</span>
                                <p class="text-sm text-gray-900">${app.applicant_email}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Telepon:</span>
                                <p class="text-sm text-gray-900">${app.applicant_phone}</p>
                            </div>
                            ${app.applicant_age ? `
                            <div>
                                <span class="text-sm font-medium text-gray-600">Umur:</span>
                                <p class="text-sm text-gray-900">${app.applicant_age} tahun</p>
                            </div>
                            ` : ''}
                            ${app.applicant_gender ? `
                            <div>
                                <span class="text-sm font-medium text-gray-600">Gender:</span>
                                <p class="text-sm text-gray-900">${app.applicant_gender}</p>
                            </div>
                            ` : ''}
                            ${app.work_experience ? `
                            <div>
                                <span class="text-sm font-medium text-gray-600">Pengalaman:</span>
                                <p class="text-sm text-gray-900">${app.work_experience}</p>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Application Details -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Detail Aplikasi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                <p class="text-sm text-gray-900">${statusBadges[app.status] || app.status}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Tipe:</span>
                                <p class="text-sm text-gray-900">${typeBadges[app.application_type] || app.application_type}</p>
                            </div>
                        </div>
                        
                        ${app.cover_letter ? `
                        <div class="mt-4">
                            <span class="text-sm font-medium text-gray-600">Surat Lamaran:</span>
                            <div class="mt-2 p-3 bg-white rounded border text-sm text-gray-700">
                                ${app.cover_letter.replace(/\n/g, '<br>')}
                            </div>
                        </div>
                        ` : ''}
                        
                        ${app.resume_path ? `
                        <div class="mt-4">
                            <span class="text-sm font-medium text-gray-600">CV:</span>
                            <div class="mt-2">
                                <a href="download_cv.php?file=${encodeURIComponent(app.resume_path.split('/').pop())}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-download mr-2"></i>Download CV
                                </a>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    
                    <!-- Job Description -->
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Deskripsi Lowongan</h4>
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Deskripsi:</span>
                                <p class="text-sm text-gray-700 mt-1">${app.description}</p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Persyaratan:</span>
                                <p class="text-sm text-gray-700 mt-1">${app.requirements}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function closeApplicationDetailModal() {
            document.getElementById('applicationDetailModal').classList.add('hidden');
        }
    </script>
</body>
</html>
