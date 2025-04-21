<?php
require 'db.php'; // Database connection

try {
    $sql = "ALTER TABLE petcare_appointments ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'";
    $conn->exec($sql);
    echo "Column 'status' added successfully to petcare_appointments table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'status' already exists in petcare_appointments table.";
    } else {
        echo "Error updating table: " . $e->getMessage();
    }
}
?>
