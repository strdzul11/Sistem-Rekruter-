<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/evaluation_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$auth = new Auth();
$criteriaManager = new CriteriaManager();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_criteria':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $weight = floatval($_POST['weight']);
                $type = sanitizeInput($_POST['type']);
                $minValue = intval($_POST['min_value']);
                $maxValue = intval($_POST['max_value']);
                
                if ($criteriaManager->addCriteria($name, $description, $weight, $type, $minValue, $maxValue)) {
                    $successMessage = "Kriteria berhasil ditambahkan";
                } else {
                    $errorMessage = "Gagal menambahkan kriteria";
                }
                break;
                
            case 'update_criteria':
                $id = intval($_POST['criteria_id']);
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $weight = floatval($_POST['weight']);
                $type = sanitizeInput($_POST['type']);
                $minValue = intval($_POST['min_value']);
                $maxValue = intval($_POST['max_value']);
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                if ($criteriaManager->updateCriteria($id, $name, $description, $weight, $type, $minValue, $maxValue, $isActive)) {
                    $successMessage = "Kriteria berhasil diupdate";
                } else {
                    $errorMessage = "Gagal mengupdate kriteria";
                }
                break;
                
            case 'delete_criteria':
                $id = intval($_POST['criteria_id']);
                if ($criteriaManager->deleteCriteria($id)) {
                    $successMessage = "Kriteria berhasil dihapus";
                } else {
                    $errorMessage = "Gagal menghapus kriteria";
                }
                break;
        }
    }
}

// Get all criteria
$criteria = $criteriaManager->getAllCriteria();
$totalWeight = $criteriaManager->validateTotalWeight();

$user = $auth->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kriteria Penilaian - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <?php require_once 'includes/sidebar.php'; renderAdminSidebar('criteria'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="h-full overflow-y-auto">
                <div class="p-8">
                    <!-- Header -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Kriteria Penilaian</h1>
                                <p class="text-gray-600 mt-2">Kelola kriteria dan bobot untuk penilaian SAW</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                                    <i class="fas fa-plus mr-2"></i>Tambah Kriteria
                                </button>
                                
                                <!-- User Profile Section -->
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-600"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                                            <p class="text-xs text-gray-500">Administrator</p>
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

                    <!-- Alert Messages -->
                    <?php if(isset($successMessage)): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i><?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($errorMessage)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <i class="fas fa-exclamation-circle mr-2"></i><?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Weight Summary -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Bobot</h3>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Bobot Kriteria Aktif</p>
                                <p class="text-2xl font-bold <?php echo $totalWeight == 100 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo number_format($totalWeight, 2); ?>%
                                </p>
                            </div>
                            <div class="text-right">
                                <?php if($totalWeight == 100): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                        <i class="fas fa-check mr-1"></i>Bobot Valid
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Bobot Tidak Valid
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">Harus total 100%</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Criteria Table -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Daftar Kriteria Penilaian</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kriteria</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Range Nilai</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($criteria as $criterion): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($criterion['name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($criterion['description']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $criterion['weight']; ?>%</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold <?php echo $criterion['type'] == 'benefit' ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100'; ?> rounded-full">
                                                <?php echo ucfirst($criterion['type']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $criterion['min_value']; ?> - <?php echo $criterion['max_value']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($criterion['is_active']): ?>
                                                <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($criterion)); ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteCriteria(<?php echo $criterion['id']; ?>, '<?php echo htmlspecialchars($criterion['name']); ?>')" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
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

    <!-- Add/Edit Modal -->
    <div id="criteriaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 mb-4">Tambah Kriteria</h3>
                    <form method="POST" id="criteriaForm">
                        <input type="hidden" name="action" id="formAction" value="add_criteria">
                        <input type="hidden" name="criteria_id" id="criteriaId">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kriteria</label>
                                <input type="text" name="name" id="criteriaName" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                                <textarea name="description" id="criteriaDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bobot (%)</label>
                                    <input type="number" name="weight" id="criteriaWeight" min="0" max="100" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe</label>
                                    <select name="type" id="criteriaType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="benefit">Benefit (Semakin tinggi semakin baik)</option>
                                        <option value="cost">Cost (Semakin rendah semakin baik)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Minimum</label>
                                    <input type="number" name="min_value" id="criteriaMinValue" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nilai Maksimum</label>
                                    <input type="number" name="max_value" id="criteriaMaxValue" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            
                            <div id="activeCheckboxDiv" class="hidden">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_active" id="criteriaActive" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeCriteriaModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-200">
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

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Kriteria';
            document.getElementById('formAction').value = 'add_criteria';
            document.getElementById('criteriaForm').reset();
            document.getElementById('activeCheckboxDiv').classList.add('hidden');
            document.getElementById('criteriaModal').classList.remove('hidden');
        }

        function openEditModal(criteria) {
            document.getElementById('modalTitle').textContent = 'Edit Kriteria';
            document.getElementById('formAction').value = 'update_criteria';
            document.getElementById('criteriaId').value = criteria.id;
            document.getElementById('criteriaName').value = criteria.name;
            document.getElementById('criteriaDescription').value = criteria.description;
            document.getElementById('criteriaWeight').value = criteria.weight;
            document.getElementById('criteriaType').value = criteria.type;
            document.getElementById('criteriaMinValue').value = criteria.min_value;
            document.getElementById('criteriaMaxValue').value = criteria.max_value;
            document.getElementById('criteriaActive').checked = criteria.is_active == 1;
            document.getElementById('activeCheckboxDiv').classList.remove('hidden');
            document.getElementById('criteriaModal').classList.remove('hidden');
        }

        function closeCriteriaModal() {
            document.getElementById('criteriaModal').classList.add('hidden');
        }

        function deleteCriteria(id, name) {
            if (confirm('Apakah Anda yakin ingin menghapus kriteria "' + name + '"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_criteria">
                    <input type="hidden" name="criteria_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    </div>
</body>
</html>
