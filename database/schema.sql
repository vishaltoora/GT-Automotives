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

-- Create sizes table
CREATE TABLE IF NOT EXISTS sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert common tire sizes
INSERT INTO sizes (name, description, sort_order) VALUES
('185/65R15', 'Common size for compact cars', 1),
('195/65R15', 'Standard size for economy vehicles', 2),
('205/65R15', 'Wider option for 15-inch wheels', 3),
('215/65R15', 'Premium size for 15-inch wheels', 4),
('185/60R16', 'Compact car size for 16-inch wheels', 5),
('195/60R16', 'Standard size for 16-inch wheels', 6),
('205/60R16', 'Wider option for 16-inch wheels', 7),
('215/60R16', 'Premium size for 16-inch wheels', 8),
('225/60R16', 'SUV size for 16-inch wheels', 9),
('195/55R16', 'Performance size for 16-inch wheels', 10),
('205/55R16', 'Sport size for 16-inch wheels', 11),
('215/55R16', 'Premium sport size for 16-inch wheels', 12),
('225/55R16', 'SUV sport size for 16-inch wheels', 13),
('235/55R16', 'Large SUV size for 16-inch wheels', 14),
('205/50R16', 'Ultra-low profile for 16-inch wheels', 15),
('215/50R16', 'Performance ultra-low profile for 16-inch wheels', 16),
('225/50R16', 'Sport ultra-low profile for 16-inch wheels', 17),
('235/50R16', 'Premium sport ultra-low profile for 16-inch wheels', 18),
('205/45R17', 'Standard size for 17-inch wheels', 19),
('215/45R17', 'Wider option for 17-inch wheels', 20),
('225/45R17', 'Premium size for 17-inch wheels', 21),
('235/45R17', 'SUV size for 17-inch wheels', 22),
('245/45R17', 'Large SUV size for 17-inch wheels', 23),
('205/40R17', 'Ultra-low profile for 17-inch wheels', 24),
('215/40R17', 'Performance ultra-low profile for 17-inch wheels', 25),
('225/40R17', 'Sport ultra-low profile for 17-inch wheels', 26),
('235/40R17', 'Premium sport ultra-low profile for 17-inch wheels', 27),
('245/40R17', 'Large sport ultra-low profile for 17-inch wheels', 28),
('215/35R17', 'Extreme low profile for 17-inch wheels', 29),
('225/35R17', 'Performance extreme low profile for 17-inch wheels', 30),
('235/35R17', 'Sport extreme low profile for 17-inch wheels', 31),
('245/35R17', 'Premium sport extreme low profile for 17-inch wheels', 32),
('215/45R18', 'Standard size for 18-inch wheels', 33),
('225/45R18', 'Wider option for 18-inch wheels', 34),
('235/45R18', 'Premium size for 18-inch wheels', 35),
('245/45R18', 'SUV size for 18-inch wheels', 36),
('255/45R18', 'Large SUV size for 18-inch wheels', 37),
('215/40R18', 'Ultra-low profile for 18-inch wheels', 38),
('225/40R18', 'Performance ultra-low profile for 18-inch wheels', 39),
('235/40R18', 'Sport ultra-low profile for 18-inch wheels', 40),
('245/40R18', 'Premium sport ultra-low profile for 18-inch wheels', 41),
('255/40R18', 'Large sport ultra-low profile for 18-inch wheels', 42),
('265/40R18', 'Extra large sport ultra-low profile for 18-inch wheels', 43),
('225/35R18', 'Extreme low profile for 18-inch wheels', 44),
('235/35R18', 'Performance extreme low profile for 18-inch wheels', 45),
('245/35R18', 'Sport extreme low profile for 18-inch wheels', 46),
('255/35R18', 'Premium sport extreme low profile for 18-inch wheels', 47),
('265/35R18', 'Large sport extreme low profile for 18-inch wheels', 48),
('275/35R18', 'Extra large sport extreme low profile for 18-inch wheels', 49),
('225/30R18', 'Ultra extreme low profile for 18-inch wheels', 50),
('235/30R18', 'Performance ultra extreme low profile for 18-inch wheels', 51),
('245/30R18', 'Sport ultra extreme low profile for 18-inch wheels', 52),
('255/30R18', 'Premium sport ultra extreme low profile for 18-inch wheels', 53),
('225/45R19', 'Standard size for 19-inch wheels', 54),
('235/45R19', 'Wider option for 19-inch wheels', 55),
('245/45R19', 'Premium size for 19-inch wheels', 56),
('255/45R19', 'SUV size for 19-inch wheels', 57),
('225/40R19', 'Ultra-low profile for 19-inch wheels', 58),
('235/40R19', 'Performance ultra-low profile for 19-inch wheels', 59),
('245/40R19', 'Sport ultra-low profile for 19-inch wheels', 60),
('255/40R19', 'Premium sport ultra-low profile for 19-inch wheels', 61),
('265/40R19', 'Large sport ultra-low profile for 19-inch wheels', 62),
('225/35R19', 'Extreme low profile for 19-inch wheels', 63),
('235/35R19', 'Performance extreme low profile for 19-inch wheels', 64),
('245/35R19', 'Sport extreme low profile for 19-inch wheels', 65),
('255/35R19', 'Premium sport extreme low profile for 19-inch wheels', 66),
('265/35R19', 'Large sport extreme low profile for 19-inch wheels', 67),
('275/35R19', 'Extra large sport extreme low profile for 19-inch wheels', 68),
('225/30R19', 'Ultra extreme low profile for 19-inch wheels', 69),
('235/30R19', 'Performance ultra extreme low profile for 19-inch wheels', 70),
('245/30R19', 'Sport ultra extreme low profile for 19-inch wheels', 71),
('255/30R19', 'Premium sport ultra extreme low profile for 19-inch wheels', 72),
('265/30R19', 'Large sport ultra extreme low profile for 19-inch wheels', 73),
('225/40R20', 'Standard size for 20-inch wheels', 74),
('235/40R20', 'Wider option for 20-inch wheels', 75),
('245/40R20', 'Premium size for 20-inch wheels', 76),
('255/40R20', 'SUV size for 20-inch wheels', 77),
('265/40R20', 'Large SUV size for 20-inch wheels', 78),
('275/40R20', 'Extra large SUV size for 20-inch wheels', 79),
('225/35R20', 'Ultra-low profile for 20-inch wheels', 80),
('235/35R20', 'Performance ultra-low profile for 20-inch wheels', 81),
('245/35R20', 'Sport ultra-low profile for 20-inch wheels', 82),
('255/35R20', 'Premium sport ultra-low profile for 20-inch wheels', 83),
('265/35R20', 'Large sport ultra-low profile for 20-inch wheels', 84),
('275/35R20', 'Extra large sport ultra-low profile for 20-inch wheels', 85),
('225/30R20', 'Extreme low profile for 20-inch wheels', 86),
('235/30R20', 'Performance extreme low profile for 20-inch wheels', 87),
('245/30R20', 'Sport extreme low profile for 20-inch wheels', 88),
('255/30R20', 'Premium sport extreme low profile for 20-inch wheels', 89),
('265/30R20', 'Large sport extreme low profile for 20-inch wheels', 90),
('275/30R20', 'Extra large sport extreme low profile for 20-inch wheels', 91),
('245/35R21', 'Standard size for 21-inch wheels', 92),
('255/35R21', 'Wider option for 21-inch wheels', 93),
('265/35R21', 'Premium size for 21-inch wheels', 94),
('275/35R21', 'SUV size for 21-inch wheels', 95),
('245/30R21', 'Ultra-low profile for 21-inch wheels', 96),
('255/30R21', 'Performance ultra-low profile for 21-inch wheels', 97),
('265/30R21', 'Sport ultra-low profile for 21-inch wheels', 98),
('275/30R21', 'Premium sport ultra-low profile for 21-inch wheels', 99),
('245/35R22', 'Standard size for 22-inch wheels', 100),
('255/35R22', 'Wider option for 22-inch wheels', 101),
('265/35R22', 'Premium size for 22-inch wheels', 102),
('275/35R22', 'SUV size for 22-inch wheels', 103),
('285/35R22', 'Large SUV size for 22-inch wheels', 104),
('245/30R22', 'Ultra-low profile for 22-inch wheels', 105),
('255/30R22', 'Performance ultra-low profile for 22-inch wheels', 106),
('265/30R22', 'Sport ultra-low profile for 22-inch wheels', 107),
('275/30R22', 'Premium sport ultra-low profile for 22-inch wheels', 108),
('285/30R22', 'Large sport ultra-low profile for 22-inch wheels', 109);

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

-- Create service_categories table
CREATE TABLE IF NOT EXISTS service_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample service categories
INSERT INTO service_categories (name, description, sort_order) VALUES
('Installation', 'Tire installation and mounting services', 1),
('Maintenance', 'Regular tire maintenance services', 2),
('Repair', 'Tire repair and emergency services', 3),
('Inspection', 'Tire inspection and safety checks', 4);

-- Create services table (updated to include category)
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_minutes INT DEFAULT 60,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category) REFERENCES service_categories(name) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample services with categories
INSERT INTO services (name, description, category, price, duration_minutes) VALUES
('Tire Installation', 'Professional tire mounting and balancing service', 'Installation', 25.00, 30),
('Wheel Alignment', 'Complete wheel alignment service', 'Maintenance', 75.00, 60),
('Tire Rotation', 'Tire rotation and balance service', 'Maintenance', 35.00, 45),
('Tire Repair', 'Puncture repair and patch service', 'Repair', 15.00, 20),
('Tire Pressure Check', 'Comprehensive tire pressure inspection', 'Inspection', 10.00, 15);

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