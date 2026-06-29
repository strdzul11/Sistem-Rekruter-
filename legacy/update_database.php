<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Update Database untuk Fitur Penilaian SAW</h2>";
    
    // Baca dan eksekusi SQL untuk tabel baru
    $sql = file_get_contents('config/database.sql');
    
    // Split SQL berdasarkan statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "<p style='color: green;'>✓ Berhasil: " . substr($statement, 0, 50) . "...</p>";
            } catch (PDOException $e) {
                // Skip jika tabel sudah ada
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "<p style='color: orange;'>⚠ Sudah ada: " . substr($statement, 0, 50) . "...</p>";
                } else {
                    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    echo "<h3>Update Database Selesai!</h3>";
    echo "<p><a href='index.php'>Kembali ke Halaman Utama</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
