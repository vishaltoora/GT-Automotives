-- SQLite schema for GT Automotives

-- Create brands table
CREATE TABLE IF NOT EXISTS brands (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT,
    website TEXT,
    logo_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    brand_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    size TEXT NOT NULL,
    price REAL NOT NULL,
    description TEXT,
    image_url TEXT,
    stock_quantity INTEGER NOT NULL DEFAULT 0,
    condition TEXT DEFAULT 'new' CHECK(condition IN ('new', 'used')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
);

-- Create used_tire_photos table for storing multiple photos per used tire
CREATE TABLE IF NOT EXISTS used_tire_photos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tire_id INTEGER NOT NULL,
    photo_url TEXT NOT NULL,
    photo_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
);

-- Insert sample tire data (updated to use brand_id)
INSERT INTO tires (brand_id, name, size, price, description, image_url, stock_quantity, condition) VALUES
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
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    email TEXT NOT NULL,
    is_admin INTEGER DEFAULT 1 AUTOINCREMENT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, is_admin) 
VALUES ('admin', '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1);

INSERT INTO users (username, password, email) VALUES (
  'rohit.toora',
  '$2y$10$.eE74/xeHHDp4ngIaWtBTexAmf7SgyjC3eanwlYoeSotwqrvyp38e',
  'rohit.toora@gmail.com'
);


-- Create inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    message TEXT NOT NULL,
    tire_id INTEGER,
    status TEXT DEFAULT 'new' CHECK(status IN ('new', 'in_progress', 'completed')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE SET NULL
);

-- Create sales table
CREATE TABLE IF NOT EXISTS sales (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_number TEXT NOT NULL UNIQUE,
    customer_name TEXT NOT NULL,
    customer_email TEXT,
    customer_phone TEXT,
    customer_address TEXT,
    customer_business_name TEXT,
    subtotal REAL NOT NULL DEFAULT 0,
    gst_rate REAL NOT NULL DEFAULT 0.05,
    gst_amount REAL NOT NULL DEFAULT 0,
    pst_rate REAL NOT NULL DEFAULT 0.07,
    pst_amount REAL NOT NULL DEFAULT 0,
    total_amount REAL NOT NULL DEFAULT 0,
    payment_method TEXT DEFAULT 'cash_with_invoice',
    payment_status TEXT DEFAULT 'pending' CHECK(payment_status IN ('pending', 'paid', 'cancelled')),
    notes TEXT,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create sale_items table
CREATE TABLE IF NOT EXISTS sale_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sale_id INTEGER NOT NULL,
    tire_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price REAL NOT NULL,
    total_price REAL NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (tire_id) REFERENCES tires(id) ON DELETE CASCADE
); 
