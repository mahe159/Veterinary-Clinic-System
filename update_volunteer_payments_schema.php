<?php
require 'db.php'; // Database connection

try {
    $sql = "
    IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='volunteer_payments' AND xtype='U')
    BEGIN
        CREATE TABLE volunteer_payments (
            id INT IDENTITY(1,1) PRIMARY KEY,
            volunteer_id INT NOT NULL,
            foster_request_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            payment_date DATETIME DEFAULT GETDATE(),
            status VARCHAR(20) DEFAULT 'paid',
            CONSTRAINT FK_volunteer FOREIGN KEY (volunteer_id) REFERENCES users(id),
            CONSTRAINT FK_foster_request FOREIGN KEY (foster_request_id) REFERENCES foster_care_requests(id)
        );
    END
    ";
    $conn->exec($sql);
    echo "Table 'volunteer_payments' created successfully or already exists.";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage();
}
?>
