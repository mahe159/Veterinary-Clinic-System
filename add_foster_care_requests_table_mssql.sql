-- SQL to create foster_care_requests table for MSSQL

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='foster_care_requests' AND xtype='U')
BEGIN
    CREATE TABLE foster_care_requests (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        pet_name VARCHAR(100) NOT NULL,
        species VARCHAR(10) NOT NULL CHECK (species IN ('cat', 'dog')),
        days INT NOT NULL,
        status VARCHAR(10) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'declined')),
        volunteer_id INT NULL,
        created_at DATETIME DEFAULT GETDATE(),
        CONSTRAINT FK_User FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT FK_Volunteer FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE SET NULL
    );
END
