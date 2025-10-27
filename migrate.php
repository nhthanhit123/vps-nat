<?php
require_once 'database.php';

// Read migration file
$migrationFile = 'database/migration_add_features.sql';
if (!file_exists($migrationFile)) {
    die("Migration file not found: $migrationFile");
}

$sql = file_get_contents($migrationFile);

// Split SQL statements by semicolon
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $conn->query($statement);
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
}

echo "\nMigration completed!\n";
?>