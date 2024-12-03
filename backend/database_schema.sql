CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    how_did_you_hear VARCHAR(255),
    is_realtor BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
