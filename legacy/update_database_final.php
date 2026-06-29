<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Update Database Final - Sistem Penilaian SAW</h2>";
    echo "<p>Menggunakan struktur database yang kompatibel dengan sistem existing dan fitur SAW</p>";
    
    // Baca dan eksekusi SQL untuk database final
    $sql = file_get_contents('database_final.sql');
    
    // Split SQL berdasarkan statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "<p style='color: green;'>✓ Berhasil: " . substr($statement, 0, 80) . "...</p>";
            } catch (PDOException $e) {
                // Skip jika tabel sudah ada atau data sudah ada
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate entry') !== false ||
                    strpos($e->getMessage(), 'Duplicate key') !== false) {
                    echo "<p style='color: orange;'>⚠ Sudah ada: " . substr($statement, 0, 80) . "...</p>";
                } else {
                    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
                    echo "<p style='color: red;'>Statement: " . substr($statement, 0, 100) . "...</p>";
                }
            }
        }
    }
    
    // Tambahkan kolom baru jika belum ada (untuk upgrade dari database lama)
    echo "<h3>Upgrade Database Existing...</h3>";
    
    $upgrades = [
        "ALTER TABLE applications ADD COLUMN applicant_age INT NULL AFTER applicant_phone",
        "ALTER TABLE applications ADD COLUMN applicant_gender ENUM('Laki-laki', 'Perempuan') DEFAULT NULL AFTER applicant_age",
        "ALTER TABLE applications ADD COLUMN work_experience VARCHAR(50) NULL AFTER applicant_gender",
        "ALTER TABLE applications ADD COLUMN application_type ENUM('registered', 'quick_apply') DEFAULT 'registered' AFTER status",
        "ALTER TABLE applications MODIFY user_id INT NULL"
    ];
    
    foreach ($upgrades as $upgrade) {
        try {
            $db->exec($upgrade);
            echo "<p style='color: green;'>✓ Upgrade: " . $upgrade . "</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                echo "<p style='color: orange;'>⚠ Kolom sudah ada: " . $upgrade . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Error upgrade: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h3>✅ Update Database Selesai!</h3>";
    echo "<p><strong>Database yang digunakan:</strong> <code>database_final.sql</code></p>";
    echo "<p>Database ini mendukung:</p>";
    echo "<ul>";
    echo "<li>✅ Aplikasi dari registered user (user_id NOT NULL)</li>";
    echo "<li>✅ Aplikasi quick apply (user_id NULL, data langsung di tabel applications)</li>";
    echo "<li>✅ Fitur penilaian SAW lengkap</li>";
    echo "<li>✅ Kompatibel dengan sistem existing</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Kembali ke Halaman Utama</a> | <a href='test_evaluation.php'>Test Sistem</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
