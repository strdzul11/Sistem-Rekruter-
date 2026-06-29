<?php
// File untuk setup database otomatis
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'rekruter_db';

try {
    // Koneksi ke MySQL tanpa database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    $pdo->exec("USE $database");
    
    // Baca dan eksekusi file SQL
    $sql = file_get_contents('database.sql');
    $statements = explode(';', $sql);
    
    foreach($statements as $statement) {
        $statement = trim($statement);
        if(!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Setup Database - Rekruter</title>
        <script src='https://cdn.tailwindcss.com'></script>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    </head>
    <body class='bg-gray-50'>
        <div class='min-h-screen flex items-center justify-center'>
            <div class='max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center'>
                <div class='w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                    <i class='fas fa-check text-green-600 text-2xl'></i>
                </div>
                <h2 class='text-2xl font-bold text-gray-900 mb-4'>Database Berhasil Dibuat!</h2>
                <p class='text-gray-600 mb-6'>Database '$database' telah dibuat dengan semua tabel dan data sample.</p>
                
                <div class='space-y-3'>
                    <a href='landing.php' class='block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200'>
                        <i class='fas fa-home mr-2'></i>Ke Halaman Utama
                    </a>
                    <a href='jobs.php' class='block w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200'>
                        <i class='fas fa-briefcase mr-2'></i>Lihat Lowongan Kerja
                    </a>
                    <a href='auth/login.php' class='block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200'>
                        <i class='fas fa-sign-in-alt mr-2'></i>Login Admin/HRD
                    </a>
                </div>
                
                <div class='mt-6 p-4 bg-blue-50 rounded-lg'>
                    <h3 class='text-sm font-semibold text-blue-900 mb-2'>Akun Demo:</h3>
                    <div class='text-xs text-blue-800 space-y-1'>
                        <div><strong>Admin:</strong> admin@rekruter.com / password</div>
                        <div><strong>HRD:</strong> hr@rekruter.com / password</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>";
    
} catch(PDOException $e) {
    echo "<!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Setup Database - Rekruter</title>
        <script src='https://cdn.tailwindcss.com'></script>
        <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    </head>
    <body class='bg-gray-50'>
        <div class='min-h-screen flex items-center justify-center'>
            <div class='max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center'>
                <div class='w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4'>
                    <i class='fas fa-exclamation-triangle text-red-600 text-2xl'></i>
                </div>
                <h2 class='text-2xl font-bold text-gray-900 mb-4'>Error Setup Database</h2>
                <p class='text-gray-600 mb-6'>Terjadi kesalahan: " . $e->getMessage() . "</p>
                <p class='text-sm text-gray-500 mb-6'>Pastikan MySQL sudah berjalan di XAMPP.</p>
                <a href='landing.php' class='bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200'>
                    <i class='fas fa-home mr-2'></i>Ke Halaman Utama
                </a>
            </div>
        </div>
    </body>
    </html>";
}
?>
