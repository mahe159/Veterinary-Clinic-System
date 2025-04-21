<?php
// This script forcibly adds the source column to the adoption_requests table if missing

require 'db.php';

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the source column exists in adoption_requests
    $result = $conn->query("SHOW COLUMNS FROM adoption_requests LIKE 'source'");
    if ($result->rowCount() == 0) {
        // Add source column as ENUM with default 'pending'
        $sql = "ALTER TABLE adoption_requests ADD COLUMN source ENUM('pending', 'cart') NOT NULL DEFAULT 'pending' AFTER status";
        $conn->exec($sql);
        echo "Column source added to adoption_requests table successfully.\n";
    } else {
        echo "Column source already exists in adoption_requests table.\n";
    }
} catch (PDOException $e) {
    echo "Error updating adoption_requests schema: " . $e->getMessage() . "\n";
}
?>
