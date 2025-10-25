CREATE DATABASE IF NOT EXISTS pgconnects;
USE pgconnects;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pgs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    rating FLOAT DEFAULT 0.0,
    discount INT DEFAULT 0,
    type VARCHAR(50) NOT NULL,
    amenities TEXT,
    image VARCHAR(255),
    femaleOnly BOOLEAN DEFAULT 0,
    vegOnly BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO pgs (title, city, price, rating, discount, type, amenities, image) VALUES
('Comfort Stay PG', 'Delhi', 8000.00, 4.2, 10, 'Single', 'WiFi,AC,Attached Bath', 'https://via.placeholder.com/300x200?text=Comfort+Stay'),
('Green Valley Hostel', 'Mumbai', 6000.00, 4.5, 5, 'Sharing', 'WiFi,Food', 'https://via.placeholder.com/300x200?text=Green+Valley'),
('City Center PG', 'Bangalore', 9000.00, 4.0, 15, 'Single', 'WiFi,AC,Laundry', 'https://via.placeholder.com/300x200?text=City+Center');