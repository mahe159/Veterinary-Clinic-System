<?php
// This script adds the volunteer_id column to the foster_care_requests table

require 'db.php';

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the column already exists to avoid errors
    $result = $conn->query("SHOW COLUMNS FROM foster_care_requests LIKE 'volunteer_id'");
    if ($result->rowCount() == 0) {
        // Add volunteer_id column as INT, nullable initially
        $sql = "ALTER TABLE foster_care_requests ADD COLUMN volunteer_id INT NULL AFTER status";
        $conn->exec($sql);
        echo "Column volunteer_id added to foster_care_requests table successfully.\n";
    } else {
        echo "Column volunteer_id already exists in foster_care_requests table.\n";
    }
} catch (PDOException $e) {
    echo "Error updating foster_care_requests schema: " . $e->getMessage() . "\n";
}
?>
