<?php
/**
 * One-time database installer.
 * Run via browser: http://localhost/aisignature-emcee/database/install.php
 * DELETE THIS FILE AFTER INSTALLATION!
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<pre style='background:#0f1629;color:#e2e8f0;padding:2rem;font-family:monospace;font-size:14px;'>";
echo "╔══════════════════════════════════════════╗\n";
echo "║  Emcee Booking System - DB Installer     ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Read SQL file
    $sqlFile = __DIR__ . '/setup.sql';
    if (!file_exists($sqlFile)) {
        echo "❌ ERROR: setup.sql not found!\n";
        exit;
    }

    $sql = file_get_contents($sqlFile);

    // Split by semicolons (simple split, works for our straightforward SQL)
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $count = 0;
    foreach ($statements as $stmt) {
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        try {
            $pdo->exec($stmt);
            $count++;
            // Show first 60 chars of each statement
            $preview = substr(preg_replace('/\s+/', ' ', $stmt), 0, 60);
            echo "✅ " . $preview . "...\n";
        } catch (PDOException $e) {
            echo "⚠️  " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 40) . "...\n";
            echo "   → " . $e->getMessage() . "\n";
        }
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ DONE! Executed $count statements.\n";
    echo "\n🔑 Admin Login: http://localhost/aisignature-emcee/admin/login.php\n";
    echo "   Username: admin\n";
    echo "   Password: admin123\n";
    echo "\n⚠️  DELETE THIS FILE after installation!\n";

} catch (PDOException $e) {
    echo "❌ DATABASE ERROR: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure XAMPP MySQL is running!\n";
}

echo "</pre>";
