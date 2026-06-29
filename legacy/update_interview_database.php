<?php
require_once 'config/database.php';

// Update database untuk fitur interview management
try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Updating Database for Interview Management Features...</h2>";
    
    // Read and execute the interview database update SQL
    $sql = file_get_contents('interview_database_update.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read interview_database_update.sql file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($statement);
            $successCount++;
            echo "<p style='color: green;'>✓ Executed: " . substr($statement, 0, 50) . "...</p>";
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Update Summary:</h3>";
    echo "<p style='color: green;'>✓ Successful statements: $successCount</p>";
    echo "<p style='color: red;'>✗ Failed statements: $errorCount</p>";
    
    if ($errorCount === 0) {
        echo "<h3 style='color: green;'>🎉 Database update completed successfully!</h3>";
        echo "<p>Interview management features are now available.</p>";
        echo "<p><a href='admin/interviews.php'>Go to Interview Management</a></p>";
    } else {
        echo "<h3 style='color: orange;'>⚠️ Database update completed with some errors.</h3>";
        echo "<p>Please check the errors above and fix them manually if needed.</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Database update failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Update - Interview Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Database Update for Interview Management</h1>
        
        <div class="bg-white rounded-lg shadow p-6">
            <?php
            // The PHP code above will execute here
            ?>
        </div>
        
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">What's New:</h3>
            <ul class="list-disc list-inside text-blue-800 space-y-1">
                <li>Interview scheduling with timezone support</li>
                <li>Google Calendar integration</li>
                <li>Video interview functionality</li>
                <li>Interview feedback forms</li>
                <li>Interview management dashboard</li>
                <li>Interview statistics and analytics</li>
            </ul>
        </div>
        
        <div class="mt-6 flex space-x-4">
            <a href="admin/interviews.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Go to Interview Management
            </a>
            <a href="admin/dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
