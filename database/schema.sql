-- MySQL/MariaDB schema for GT Automotives

-- Create brands table
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    website VARCHAR(500),
    logo_url VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample brand data
INSERT INTO brands (name, description, website) VALUES
('Michelin', 'French tire manufacturer known for high-performance and innovative tire technology.', 'https://www.michelin.com'),
('Bridgestone', 'Japanese multinational tire and rubber company, the largest tire manufacturer in the world.', 'https://www.bridgestone.com'),
('Goodyear', 'American multinational tire manufacturing company founded in 1898.', 'https://www.goodyear.com'),
('Continental', 'German automotive manufacturing company specializing in brake systems, interior electronics, and tires.', 'https://www.continental-tires.com'),
('Pirelli', 'Italian multinational tire manufacturer focused on high-performance and luxury vehicles.', 'https://www.pirelli.com'),
('Yokohama', 'Japanese tire manufacturer known for high-performance and all-season tires.', 'https://www.yokohamatire.com'),
('Toyo', 'Japanese tire manufacturer specializing in performance and off-road tires.', 'https://www.toyotires.com'),
('Hankook', 'South Korean tire manufacturer known for quality and performance at competitive prices.', 'https://www.hankooktire.com');

-- Create tires table (updated to include condition field)
CREATE TABLE IF NOT EXISTS tires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    size VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    stock_quantity INT NOT NULL DEFAULT 0,
    `condition` ENUM('new', 'used') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create used_tire_photos table for storing multiple photos per used tire
CREATE TABLE IF NOT EXISTS used_tire_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tire_id INT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    photo_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample tire data (updated to use brand_id)
INSERT INTO tires (brand_id, name, size, price, description, image_url, stock_quantity, `condition`) VALUES
(1, 'Pilot Sport 4S', '225/45R17', 199.99, 'High-performance summer tire with excellent grip and handling.', 'images/tires/michelin-pilot-sport-4s.jpg', 50, 'new'),
(2, 'Potenza RE-71R', '245/40R18', 189.99, 'Ultra-high performance summer tire for track and street use.', 'images/tires/bridgestone-potenza-re71r.jpg', 40, 'new'),
(4, 'ExtremeContact DWS06', '235/45R17', 179.99, 'All-season ultra-high performance tire with excellent wet and dry handling.', 'images/tires/continental-dws06.jpg', 45, 'new'),
(3, 'Eagle F1 Asymmetric 5', '255/35R19', 219.99, 'Premium summer tire with exceptional cornering stability.', 'images/tires/goodyear-eagle-f1.jpg', 35, 'new'),
(5, 'P Zero', '265/30R20', 229.99, 'High-performance summer tire with precise steering response.', 'images/tires/pirelli-p-zero.jpg', 30, 'new'),
(6, 'ADVAN Sport V105', '245/40R18', 189.99, 'Ultra-high performance summer tire with excellent grip.', 'images/tires/yokohama-advan-sport.jpg', 40, 'new'),
(7, 'Proxes R888R', '275/35R18', 199.99, 'Track-focused tire with maximum grip and durability.', 'images/tires/toyo-proxes-r888r.jpg', 25, 'new'),
(8, 'Ventus V12 evo2', '225/45R17', 159.99, 'High-performance summer tire with balanced handling.', 'images/tires/hankook-ventus-v12.jpg', 55, 'new');

-- Create users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, is_admin) 
VALUES ('admin', '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1);

-- Create inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    tire_id INT,
    status ENUM('new', 'in_progress', 'completed') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sales table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(100) NOT NULL UNIQUE,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255),
    customer_phone VARCHAR(50),
    customer_address TEXT,
    customer_business_name VARCHAR(255),
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    gst_rate DECIMAL(5,4) NOT NULL DEFAULT 0.05,
    gst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    pst_rate DECIMAL(5,4) NOT NULL DEFAULT 0.07,
    pst_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash_with_invoice', 'credit_card', 'bank_transfer') DEFAULT 'cash_with_invoice',
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sale_items table
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    tire_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_minutes INT DEFAULT 60,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample services
INSERT INTO services (name, description, price, duration_minutes) VALUES
('Tire Installation', 'Professional tire mounting and balancing service', 25.00, 30),
('Wheel Alignment', 'Complete wheel alignment service', 75.00, 60),
('Tire Rotation', 'Tire rotation and balance service', 35.00, 45),
('Tire Repair', 'Puncture repair and patch service', 15.00, 20),
('Tire Pressure Check', 'Comprehensive tire pressure inspection', 10.00, 15);

-- Create locations table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    hours TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample location
INSERT INTO locations (name, address, phone, email, hours) VALUES
('GT Automotives Main Store', '123 Main Street, Vancouver, BC V6B 1A1', '(604) 555-0123', 'info@gtautomotives.com', 'Mon-Fri: 8AM-6PM, Sat: 9AM-5PM, Sun: Closed'); 