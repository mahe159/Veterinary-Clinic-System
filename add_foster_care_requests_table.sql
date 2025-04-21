-- SQL to create foster_care_requests table

CREATE TABLE IF NOT EXISTS foster_care_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_name VARCHAR(100) NOT NULL,
    species ENUM('cat', 'dog') NOT NULL,
    days INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    volunteer_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE SET NULL
);
