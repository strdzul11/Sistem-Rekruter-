<?php
/**
 * Admin Sidebar Component
 * 
 * @param string $activePage - Nama halaman yang sedang aktif (untuk highlight menu)
 */
function renderAdminSidebar($activePage = '') {
    // Fungsi helper untuk menentukan class menu item
    function getMenuItemClass($page, $activePage) {
        return ($page === $activePage) 
            ? 'flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600'
            : 'flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50';
    }
    ?>
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-lg">
        <div class="p-6">
            <div class="flex items-center">
                <img src="../logo.png" alt="PT. PUTRI KEBUN LESTARI" class="w-10 h-10 object-contain">
                <h1 class="ml-3 text-xl font-bold text-gray-900">PT. PUTRI KEBUN LESTARI</h1>
            </div>
        </div>
        
        <nav class="mt-6">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Menu Utama</p>
            </div>
            <a href="dashboard.php" class="<?php echo getMenuItemClass('dashboard', $activePage); ?>">
                <i class="fas fa-tachometer-alt mr-3"></i>
                Dashboard
            </a>
            <a href="users.php" class="<?php echo getMenuItemClass('users', $activePage); ?>">
                <i class="fas fa-users mr-3"></i>
                Manajemen User
            </a>
            <a href="jobs.php" class="<?php echo getMenuItemClass('jobs', $activePage); ?>">
                <i class="fas fa-briefcase mr-3"></i>
                Lowongan Kerja
            </a>
            <a href="applications.php" class="<?php echo getMenuItemClass('applications', $activePage); ?>">
                <i class="fas fa-file-alt mr-3"></i>
                Aplikasi
            </a>
            
            <div class="px-6 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Sistem Penilaian</p>
            </div>
            <a href="evaluations.php" class="<?php echo getMenuItemClass('evaluations', $activePage); ?>">
                <i class="fas fa-star mr-3"></i>
                Penilaian Pelamar
            </a>
            <a href="rankings.php" class="<?php echo getMenuItemClass('rankings', $activePage); ?>">
                <i class="fas fa-trophy mr-3"></i>
                Ranking & Hasil
            </a>
            <a href="criteria.php" class="<?php echo getMenuItemClass('criteria', $activePage); ?>">
                <i class="fas fa-cogs mr-3"></i>
                Kriteria Penilaian
            </a>
            
            <div class="px-6 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Interview</p>
            </div>
            <a href="interviews.php" class="<?php echo getMenuItemClass('interviews', $activePage); ?>">
                <i class="fas fa-video mr-3"></i>
                Manajemen Interview
            </a>
            
            <div class="px-6 py-2 mt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lainnya</p>
            </div>
            <a href="reports.php" class="<?php echo getMenuItemClass('reports', $activePage); ?>">
                <i class="fas fa-chart-bar mr-3"></i>
                Laporan
            </a>
        </nav>
    </div>
    <?php
}
?>

