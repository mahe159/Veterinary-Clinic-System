CREATE TABLE IF NOT EXISTS volunteer_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    foster_request_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'paid',
    FOREIGN KEY (volunteer_id) REFERENCES users(id),
    FOREIGN KEY (foster_request_id) REFERENCES foster_care_requests(id)
);
