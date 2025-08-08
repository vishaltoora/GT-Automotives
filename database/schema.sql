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
-- 13-inch sizes (compact cars)
('155/80R13', 'Compact car size for 13-inch wheels', 1),
('165/80R13', 'Standard compact car size for 13-inch wheels', 2),
('175/70R13', 'Wider compact car size for 13-inch wheels', 3),
('185/70R13', 'Premium compact car size for 13-inch wheels', 4),

-- 14-inch sizes (economy cars)
('165/70R14', 'Economy car size for 14-inch wheels', 5),
('175/70R14', 'Standard economy car size for 14-inch wheels', 6),
('185/70R14', 'Wider economy car size for 14-inch wheels', 7),
('195/70R14', 'Premium economy car size for 14-inch wheels', 8),
('205/70R14', 'SUV size for 14-inch wheels', 9),
('165/65R14', 'Performance economy car size for 14-inch wheels', 10),
('175/65R14', 'Sport economy car size for 14-inch wheels', 11),
('185/65R14', 'Premium sport economy car size for 14-inch wheels', 12),

-- 15-inch sizes (compact and economy cars)
('185/65R15', 'Common size for compact cars', 13),
('195/65R15', 'Standard size for economy vehicles', 14),
('205/65R15', 'Wider option for 15-inch wheels', 15),
('215/65R15', 'Premium size for 15-inch wheels', 16),
('225/65R15', 'SUV size for 15-inch wheels', 17),
('235/65R15', 'Large SUV size for 15-inch wheels', 18),
('185/60R15', 'Performance compact car size for 15-inch wheels', 19),
('195/60R15', 'Sport compact car size for 15-inch wheels', 20),
('205/60R15', 'Premium sport compact car size for 15-inch wheels', 21),
('215/60R15', 'Ultra sport compact car size for 15-inch wheels', 22),
('185/55R15', 'Ultra-low profile for 15-inch wheels', 23),
('195/55R15', 'Performance ultra-low profile for 15-inch wheels', 24),
('205/55R15', 'Sport ultra-low profile for 15-inch wheels', 25),
('215/55R15', 'Premium sport ultra-low profile for 15-inch wheels', 26),

-- 16-inch sizes (compact and mid-size cars)
('185/60R16', 'Compact car size for 16-inch wheels', 27),
('195/60R16', 'Standard size for 16-inch wheels', 28),
('205/60R16', 'Wider option for 16-inch wheels', 29),
('215/60R16', 'Premium size for 16-inch wheels', 30),
('225/60R16', 'SUV size for 16-inch wheels', 31),
('235/60R16', 'Large SUV size for 16-inch wheels', 32),
('245/60R16', 'Extra large SUV size for 16-inch wheels', 33),
('195/55R16', 'Performance size for 16-inch wheels', 34),
('205/55R16', 'Sport size for 16-inch wheels', 35),
('215/55R16', 'Premium sport size for 16-inch wheels', 36),
('225/55R16', 'SUV sport size for 16-inch wheels', 37),
('235/55R16', 'Large SUV sport size for 16-inch wheels', 38),
('245/55R16', 'Extra large SUV sport size for 16-inch wheels', 39),
('205/50R16', 'Ultra-low profile for 16-inch wheels', 40),
('215/50R16', 'Performance ultra-low profile for 16-inch wheels', 41),
('225/50R16', 'Sport ultra-low profile for 16-inch wheels', 42),
('235/50R16', 'Premium sport ultra-low profile for 16-inch wheels', 43),
('245/50R16', 'Large sport ultra-low profile for 16-inch wheels', 44),
('205/45R16', 'Extreme low profile for 16-inch wheels', 45),
('215/45R16', 'Performance extreme low profile for 16-inch wheels', 46),
('225/45R16', 'Sport extreme low profile for 16-inch wheels', 47),
('235/45R16', 'Premium sport extreme low profile for 16-inch wheels', 48),
('245/45R16', 'Large sport extreme low profile for 16-inch wheels', 49),

-- 17-inch sizes (mid-size and luxury cars)
('205/45R17', 'Standard size for 17-inch wheels', 50),
('215/45R17', 'Wider option for 17-inch wheels', 51),
('225/45R17', 'Premium size for 17-inch wheels', 52),
('235/45R17', 'SUV size for 17-inch wheels', 53),
('245/45R17', 'Large SUV size for 17-inch wheels', 54),
('255/45R17', 'Extra large SUV size for 17-inch wheels', 55),
('205/40R17', 'Ultra-low profile for 17-inch wheels', 56),
('215/40R17', 'Performance ultra-low profile for 17-inch wheels', 57),
('225/40R17', 'Sport ultra-low profile for 17-inch wheels', 58),
('235/40R17', 'Premium sport ultra-low profile for 17-inch wheels', 59),
('245/40R17', 'Large sport ultra-low profile for 17-inch wheels', 60),
('255/40R17', 'Extra large sport ultra-low profile for 17-inch wheels', 61),
('215/35R17', 'Extreme low profile for 17-inch wheels', 62),
('225/35R17', 'Performance extreme low profile for 17-inch wheels', 63),
('235/35R17', 'Sport extreme low profile for 17-inch wheels', 64),
('245/35R17', 'Premium sport extreme low profile for 17-inch wheels', 65),
('255/35R17', 'Large sport extreme low profile for 17-inch wheels', 66),
('265/35R17', 'Extra large sport extreme low profile for 17-inch wheels', 67),

-- 18-inch sizes (luxury and performance cars)
('215/45R18', 'Standard size for 18-inch wheels', 68),
('225/45R18', 'Wider option for 18-inch wheels', 69),
('235/45R18', 'Premium size for 18-inch wheels', 70),
('245/45R18', 'SUV size for 18-inch wheels', 71),
('255/45R18', 'Large SUV size for 18-inch wheels', 72),
('265/45R18', 'Extra large SUV size for 18-inch wheels', 73),
('275/45R18', 'Ultra large SUV size for 18-inch wheels', 74),
('215/40R18', 'Ultra-low profile for 18-inch wheels', 75),
('225/40R18', 'Performance ultra-low profile for 18-inch wheels', 76),
('235/40R18', 'Sport ultra-low profile for 18-inch wheels', 77),
('245/40R18', 'Premium sport ultra-low profile for 18-inch wheels', 78),
('255/40R18', 'Large sport ultra-low profile for 18-inch wheels', 79),
('265/40R18', 'Extra large sport ultra-low profile for 18-inch wheels', 80),
('275/40R18', 'Ultra large sport ultra-low profile for 18-inch wheels', 81),
('225/35R18', 'Extreme low profile for 18-inch wheels', 82),
('235/35R18', 'Performance extreme low profile for 18-inch wheels', 83),
('245/35R18', 'Sport extreme low profile for 18-inch wheels', 84),
('255/35R18', 'Premium sport extreme low profile for 18-inch wheels', 85),
('265/35R18', 'Large sport extreme low profile for 18-inch wheels', 86),
('275/35R18', 'Extra large sport extreme low profile for 18-inch wheels', 87),
('285/35R18', 'Ultra large sport extreme low profile for 18-inch wheels', 88),
('225/30R18', 'Ultra extreme low profile for 18-inch wheels', 89),
('235/30R18', 'Performance ultra extreme low profile for 18-inch wheels', 90),
('245/30R18', 'Sport ultra extreme low profile for 18-inch wheels', 91),
('255/30R18', 'Premium sport ultra extreme low profile for 18-inch wheels', 92),
('265/30R18', 'Large sport ultra extreme low profile for 18-inch wheels', 93),
('275/30R18', 'Extra large sport ultra extreme low profile for 18-inch wheels', 94),

-- 19-inch sizes (luxury and high-performance cars)
('225/45R19', 'Standard size for 19-inch wheels', 95),
('235/45R19', 'Wider option for 19-inch wheels', 96),
('245/45R19', 'Premium size for 19-inch wheels', 97),
('255/45R19', 'SUV size for 19-inch wheels', 98),
('265/45R19', 'Large SUV size for 19-inch wheels', 99),
('275/45R19', 'Extra large SUV size for 19-inch wheels', 100),
('285/45R19', 'Ultra large SUV size for 19-inch wheels', 101),
('225/40R19', 'Ultra-low profile for 19-inch wheels', 102),
('235/40R19', 'Performance ultra-low profile for 19-inch wheels', 103),
('245/40R19', 'Sport ultra-low profile for 19-inch wheels', 104),
('255/40R19', 'Premium sport ultra-low profile for 19-inch wheels', 105),
('265/40R19', 'Large sport ultra-low profile for 19-inch wheels', 106),
('275/40R19', 'Extra large sport ultra-low profile for 19-inch wheels', 107),
('285/40R19', 'Ultra large sport ultra-low profile for 19-inch wheels', 108),
('225/35R19', 'Extreme low profile for 19-inch wheels', 109),
('235/35R19', 'Performance extreme low profile for 19-inch wheels', 110),
('245/35R19', 'Sport extreme low profile for 19-inch wheels', 111),
('255/35R19', 'Premium sport extreme low profile for 19-inch wheels', 112),
('265/35R19', 'Large sport extreme low profile for 19-inch wheels', 113),
('275/35R19', 'Extra large sport extreme low profile for 19-inch wheels', 114),
('285/35R19', 'Ultra large sport extreme low profile for 19-inch wheels', 115),
('225/30R19', 'Ultra extreme low profile for 19-inch wheels', 116),
('235/30R19', 'Performance ultra extreme low profile for 19-inch wheels', 117),
('245/30R19', 'Sport ultra extreme low profile for 19-inch wheels', 118),
('255/30R19', 'Premium sport ultra extreme low profile for 19-inch wheels', 119),
('265/30R19', 'Large sport ultra extreme low profile for 19-inch wheels', 120),
('275/30R19', 'Extra large sport ultra extreme low profile for 19-inch wheels', 121),

-- 20-inch sizes (luxury and ultra-high-performance cars)
('225/40R20', 'Standard size for 20-inch wheels', 122),
('235/40R20', 'Wider option for 20-inch wheels', 123),
('245/40R20', 'Premium size for 20-inch wheels', 124),
('255/40R20', 'SUV size for 20-inch wheels', 125),
('265/40R20', 'Large SUV size for 20-inch wheels', 126),
('275/40R20', 'Extra large SUV size for 20-inch wheels', 127),
('285/40R20', 'Ultra large SUV size for 20-inch wheels', 128),
('295/40R20', 'Mega large SUV size for 20-inch wheels', 129),
('225/35R20', 'Ultra-low profile for 20-inch wheels', 130),
('235/35R20', 'Performance ultra-low profile for 20-inch wheels', 131),
('245/35R20', 'Sport ultra-low profile for 20-inch wheels', 132),
('255/35R20', 'Premium sport ultra-low profile for 20-inch wheels', 133),
('265/35R20', 'Large sport ultra-low profile for 20-inch wheels', 134),
('275/35R20', 'Extra large sport ultra-low profile for 20-inch wheels', 135),
('285/35R20', 'Ultra large sport ultra-low profile for 20-inch wheels', 136),
('295/35R20', 'Mega large sport ultra-low profile for 20-inch wheels', 137),
('225/30R20', 'Extreme low profile for 20-inch wheels', 138),
('235/30R20', 'Performance extreme low profile for 20-inch wheels', 139),
('245/30R20', 'Sport extreme low profile for 20-inch wheels', 140),
('255/30R20', 'Premium sport extreme low profile for 20-inch wheels', 141),
('265/30R20', 'Large sport extreme low profile for 20-inch wheels', 142),
('275/30R20', 'Extra large sport extreme low profile for 20-inch wheels', 143),
('285/30R20', 'Ultra large sport extreme low profile for 20-inch wheels', 144),
('295/30R20', 'Mega large sport extreme low profile for 20-inch wheels', 145),

-- 21-inch sizes (ultra-luxury cars)
('245/35R21', 'Standard size for 21-inch wheels', 146),
('255/35R21', 'Wider option for 21-inch wheels', 147),
('265/35R21', 'Premium size for 21-inch wheels', 148),
('275/35R21', 'SUV size for 21-inch wheels', 149),
('285/35R21', 'Large SUV size for 21-inch wheels', 150),
('295/35R21', 'Extra large SUV size for 21-inch wheels', 151),
('305/35R21', 'Ultra large SUV size for 21-inch wheels', 152),
('245/30R21', 'Ultra-low profile for 21-inch wheels', 153),
('255/30R21', 'Performance ultra-low profile for 21-inch wheels', 154),
('265/30R21', 'Sport ultra-low profile for 21-inch wheels', 155),
('275/30R21', 'Premium sport ultra-low profile for 21-inch wheels', 156),
('285/30R21', 'Large sport ultra-low profile for 21-inch wheels', 157),
('295/30R21', 'Extra large sport ultra-low profile for 21-inch wheels', 158),
('305/30R21', 'Ultra large sport ultra-low profile for 21-inch wheels', 159),

-- 22-inch sizes (ultra-luxury and exotic cars)
('245/35R22', 'Standard size for 22-inch wheels', 160),
('255/35R22', 'Wider option for 22-inch wheels', 161),
('265/35R22', 'Premium size for 22-inch wheels', 162),
('275/35R22', 'SUV size for 22-inch wheels', 163),
('285/35R22', 'Large SUV size for 22-inch wheels', 164),
('295/35R22', 'Extra large SUV size for 22-inch wheels', 165),
('305/35R22', 'Ultra large SUV size for 22-inch wheels', 166),
('315/35R22', 'Mega large SUV size for 22-inch wheels', 167),
('245/30R22', 'Ultra-low profile for 22-inch wheels', 168),
('255/30R22', 'Performance ultra-low profile for 22-inch wheels', 169),
('265/30R22', 'Sport ultra-low profile for 22-inch wheels', 170),
('275/30R22', 'Premium sport ultra-low profile for 22-inch wheels', 171),
('285/30R22', 'Large sport ultra-low profile for 22-inch wheels', 172),
('295/30R22', 'Extra large sport ultra-low profile for 22-inch wheels', 173),
('305/30R22', 'Ultra large sport ultra-low profile for 22-inch wheels', 174),
('315/30R22', 'Mega large sport ultra-low profile for 22-inch wheels', 175),

-- 23-inch sizes (exotic cars)
('255/35R23', 'Standard size for 23-inch wheels', 176),
('265/35R23', 'Wider option for 23-inch wheels', 177),
('275/35R23', 'Premium size for 23-inch wheels', 178),
('285/35R23', 'SUV size for 23-inch wheels', 179),
('295/35R23', 'Large SUV size for 23-inch wheels', 180),
('305/35R23', 'Extra large SUV size for 23-inch wheels', 181),
('255/30R23', 'Ultra-low profile for 23-inch wheels', 182),
('265/30R23', 'Performance ultra-low profile for 23-inch wheels', 183),
('275/30R23', 'Sport ultra-low profile for 23-inch wheels', 184),
('285/30R23', 'Premium sport ultra-low profile for 23-inch wheels', 185),
('295/30R23', 'Large sport ultra-low profile for 23-inch wheels', 186),
('305/30R23', 'Extra large sport ultra-low profile for 23-inch wheels', 187),

-- 24-inch sizes (exotic and custom cars)
('255/35R24', 'Standard size for 24-inch wheels', 188),
('265/35R24', 'Wider option for 24-inch wheels', 189),
('275/35R24', 'Premium size for 24-inch wheels', 190),
('285/35R24', 'SUV size for 24-inch wheels', 191),
('295/35R24', 'Large SUV size for 24-inch wheels', 192),
('305/35R24', 'Extra large SUV size for 24-inch wheels', 193),
('255/30R24', 'Ultra-low profile for 24-inch wheels', 194),
('265/30R24', 'Performance ultra-low profile for 24-inch wheels', 195),
('275/30R24', 'Sport ultra-low profile for 24-inch wheels', 196),
('285/30R24', 'Premium sport ultra-low profile for 24-inch wheels', 197),
('295/30R24', 'Large sport ultra-low profile for 24-inch wheels', 198),
('305/30R24', 'Extra large sport ultra-low profile for 24-inch wheels', 199),

-- Light Truck (LT) sizes for pickup trucks and SUVs
-- 15-inch LT sizes
('LT215/75R15', 'Light truck size for 15-inch wheels', 200),
('LT225/75R15', 'Standard light truck size for 15-inch wheels', 201),
('LT235/75R15', 'Wider light truck size for 15-inch wheels', 202),
('LT245/75R15', 'Premium light truck size for 15-inch wheels', 203),
('LT255/75R15', 'Large light truck size for 15-inch wheels', 204),
('LT265/75R15', 'Extra large light truck size for 15-inch wheels', 205),
('LT275/75R15', 'Ultra large light truck size for 15-inch wheels', 206),
('LT285/75R15', 'Mega large light truck size for 15-inch wheels', 207),

-- 16-inch LT sizes
('LT215/75R16', 'Light truck size for 16-inch wheels', 208),
('LT225/75R16', 'Standard light truck size for 16-inch wheels', 209),
('LT235/75R16', 'Wider light truck size for 16-inch wheels', 210),
('LT245/75R16', 'Premium light truck size for 16-inch wheels', 211),
('LT255/75R16', 'Large light truck size for 16-inch wheels', 212),
('LT265/75R16', 'Extra large light truck size for 16-inch wheels', 213),
('LT275/75R16', 'Ultra large light truck size for 16-inch wheels', 214),
('LT285/75R16', 'Mega large light truck size for 16-inch wheels', 215),
('LT295/75R16', 'Super large light truck size for 16-inch wheels', 216),
('LT305/75R16', 'Ultra super large light truck size for 16-inch wheels', 217),
('LT315/75R16', 'Mega super large light truck size for 16-inch wheels', 218),

-- 17-inch LT sizes
('LT225/75R17', 'Light truck size for 17-inch wheels', 219),
('LT235/75R17', 'Standard light truck size for 17-inch wheels', 220),
('LT245/75R17', 'Wider light truck size for 17-inch wheels', 221),
('LT255/75R17', 'Premium light truck size for 17-inch wheels', 222),
('LT265/75R17', 'Large light truck size for 17-inch wheels', 223),
('LT275/75R17', 'Extra large light truck size for 17-inch wheels', 224),
('LT285/75R17', 'Ultra large light truck size for 17-inch wheels', 225),
('LT295/75R17', 'Mega large light truck size for 17-inch wheels', 226),
('LT305/75R17', 'Super large light truck size for 17-inch wheels', 227),
('LT315/75R17', 'Ultra super large light truck size for 17-inch wheels', 228),
('LT325/75R17', 'Mega super large light truck size for 17-inch wheels', 229),

-- 18-inch LT sizes
('LT235/75R18', 'Light truck size for 18-inch wheels', 230),
('LT245/75R18', 'Standard light truck size for 18-inch wheels', 231),
('LT255/75R18', 'Wider light truck size for 18-inch wheels', 232),
('LT265/75R18', 'Premium light truck size for 18-inch wheels', 233),
('LT275/75R18', 'Large light truck size for 18-inch wheels', 234),
('LT285/75R18', 'Extra large light truck size for 18-inch wheels', 235),
('LT295/75R18', 'Ultra large light truck size for 18-inch wheels', 236),
('LT305/75R18', 'Mega large light truck size for 18-inch wheels', 237),
('LT315/75R18', 'Super large light truck size for 18-inch wheels', 238),
('LT325/75R18', 'Ultra super large light truck size for 18-inch wheels', 239),
('LT335/75R18', 'Mega super large light truck size for 18-inch wheels', 240),

-- 20-inch LT sizes
('LT245/75R20', 'Light truck size for 20-inch wheels', 241),
('LT255/75R20', 'Standard light truck size for 20-inch wheels', 242),
('LT265/75R20', 'Wider light truck size for 20-inch wheels', 243),
('LT275/75R20', 'Premium light truck size for 20-inch wheels', 244),
('LT285/75R20', 'Large light truck size for 20-inch wheels', 245),
('LT295/75R20', 'Extra large light truck size for 20-inch wheels', 246),
('LT305/75R20', 'Ultra large light truck size for 20-inch wheels', 247),
('LT315/75R20', 'Mega large light truck size for 20-inch wheels', 248),
('LT325/75R20', 'Super large light truck size for 20-inch wheels', 249),
('LT335/75R20', 'Ultra super large light truck size for 20-inch wheels', 250),
('LT345/75R20', 'Mega super large light truck size for 20-inch wheels', 251),

-- 22-inch LT sizes
('LT255/75R22', 'Light truck size for 22-inch wheels', 252),
('LT265/75R22', 'Standard light truck size for 22-inch wheels', 253),
('LT275/75R22', 'Wider light truck size for 22-inch wheels', 254),
('LT285/75R22', 'Premium light truck size for 22-inch wheels', 255),
('LT295/75R22', 'Large light truck size for 22-inch wheels', 256),
('LT305/75R22', 'Extra large light truck size for 22-inch wheels', 257),
('LT315/75R22', 'Ultra large light truck size for 22-inch wheels', 258),
('LT325/75R22', 'Mega large light truck size for 22-inch wheels', 259),
('LT335/75R22', 'Super large light truck size for 22-inch wheels', 260),
('LT345/75R22', 'Ultra super large light truck size for 22-inch wheels', 261),
('LT355/75R22', 'Mega super large light truck size for 22-inch wheels', 262),

-- 24-inch LT sizes
('LT265/75R24', 'Light truck size for 24-inch wheels', 263),
('LT275/75R24', 'Standard light truck size for 24-inch wheels', 264),
('LT285/75R24', 'Wider light truck size for 24-inch wheels', 265),
('LT295/75R24', 'Premium light truck size for 24-inch wheels', 266),
('LT305/75R24', 'Large light truck size for 24-inch wheels', 267),
('LT315/75R24', 'Extra large light truck size for 24-inch wheels', 268),
('LT325/75R24', 'Ultra large light truck size for 24-inch wheels', 269),
('LT335/75R24', 'Mega large light truck size for 24-inch wheels', 270),
('LT345/75R24', 'Super large light truck size for 24-inch wheels', 271),
('LT355/75R24', 'Ultra super large light truck size for 24-inch wheels', 272),
('LT365/75R24', 'Mega super large light truck size for 24-inch wheels', 273),

-- 26-inch LT sizes
('LT275/75R26', 'Light truck size for 26-inch wheels', 274),
('LT285/75R26', 'Standard light truck size for 26-inch wheels', 275),
('LT295/75R26', 'Wider light truck size for 26-inch wheels', 276),
('LT305/75R26', 'Premium light truck size for 26-inch wheels', 277),
('LT315/75R26', 'Large light truck size for 26-inch wheels', 278),
('LT325/75R26', 'Extra large light truck size for 26-inch wheels', 279),
('LT335/75R26', 'Ultra large light truck size for 26-inch wheels', 280),
('LT345/75R26', 'Mega large light truck size for 26-inch wheels', 281),
('LT355/75R26', 'Super large light truck size for 26-inch wheels', 282),
('LT365/75R26', 'Ultra super large light truck size for 26-inch wheels', 283),
('LT375/75R26', 'Mega super large light truck size for 26-inch wheels', 284),

-- 28-inch LT sizes
('LT285/75R28', 'Light truck size for 28-inch wheels', 285),
('LT295/75R28', 'Standard light truck size for 28-inch wheels', 286),
('LT305/75R28', 'Wider light truck size for 28-inch wheels', 287),
('LT315/75R28', 'Premium light truck size for 28-inch wheels', 288),
('LT325/75R28', 'Large light truck size for 28-inch wheels', 289),
('LT335/75R28', 'Extra large light truck size for 28-inch wheels', 290),
('LT345/75R28', 'Ultra large light truck size for 28-inch wheels', 291),
('LT355/75R28', 'Mega large light truck size for 28-inch wheels', 292),
('LT365/75R28', 'Super large light truck size for 28-inch wheels', 293),
('LT375/75R28', 'Ultra super large light truck size for 28-inch wheels', 294),
('LT385/75R28', 'Mega super large light truck size for 28-inch wheels', 295),

-- 30-inch LT sizes
('LT295/75R30', 'Light truck size for 30-inch wheels', 296),
('LT305/75R30', 'Standard light truck size for 30-inch wheels', 297),
('LT315/75R30', 'Wider light truck size for 30-inch wheels', 298),
('LT325/75R30', 'Premium light truck size for 30-inch wheels', 299),
('LT335/75R30', 'Large light truck size for 30-inch wheels', 300),
('LT345/75R30', 'Extra large light truck size for 30-inch wheels', 301),
('LT355/75R30', 'Ultra large light truck size for 30-inch wheels', 302),
('LT365/75R30', 'Mega large light truck size for 30-inch wheels', 303),
('LT375/75R30', 'Super large light truck size for 30-inch wheels', 304),
('LT385/75R30', 'Ultra super large light truck size for 30-inch wheels', 305),
('LT395/75R30', 'Mega super large light truck size for 30-inch wheels', 306),

-- 32-inch LT sizes
('LT305/75R32', 'Light truck size for 32-inch wheels', 307),
('LT315/75R32', 'Standard light truck size for 32-inch wheels', 308),
('LT325/75R32', 'Wider light truck size for 32-inch wheels', 309),
('LT335/75R32', 'Premium light truck size for 32-inch wheels', 310),
('LT345/75R32', 'Large light truck size for 32-inch wheels', 311),
('LT355/75R32', 'Extra large light truck size for 32-inch wheels', 312),
('LT365/75R32', 'Ultra large light truck size for 32-inch wheels', 313),
('LT375/75R32', 'Mega large light truck size for 32-inch wheels', 314),
('LT385/75R32', 'Super large light truck size for 32-inch wheels', 315),
('LT395/75R32', 'Ultra super large light truck size for 32-inch wheels', 316),
('LT405/75R32', 'Mega super large light truck size for 32-inch wheels', 317),

-- 35-inch LT sizes
('LT315/75R35', 'Light truck size for 35-inch wheels', 318),
('LT325/75R35', 'Standard light truck size for 35-inch wheels', 319),
('LT335/75R35', 'Wider light truck size for 35-inch wheels', 320),
('LT345/75R35', 'Premium light truck size for 35-inch wheels', 321),
('LT355/75R35', 'Large light truck size for 35-inch wheels', 322),
('LT365/75R35', 'Extra large light truck size for 35-inch wheels', 323),
('LT375/75R35', 'Ultra large light truck size for 35-inch wheels', 324),
('LT385/75R35', 'Mega large light truck size for 35-inch wheels', 325),
('LT395/75R35', 'Super large light truck size for 35-inch wheels', 326),
('LT405/75R35', 'Ultra super large light truck size for 35-inch wheels', 327),
('LT415/75R35', 'Mega super large light truck size for 35-inch wheels', 328),

-- 37-inch LT sizes
('LT325/75R37', 'Light truck size for 37-inch wheels', 329),
('LT335/75R37', 'Standard light truck size for 37-inch wheels', 330),
('LT345/75R37', 'Wider light truck size for 37-inch wheels', 331),
('LT355/75R37', 'Premium light truck size for 37-inch wheels', 332),
('LT365/75R37', 'Large light truck size for 37-inch wheels', 333),
('LT375/75R37', 'Extra large light truck size for 37-inch wheels', 334),
('LT385/75R37', 'Ultra large light truck size for 37-inch wheels', 335),
('LT395/75R37', 'Mega large light truck size for 37-inch wheels', 336),
('LT405/75R37', 'Super large light truck size for 37-inch wheels', 337),
('LT415/75R37', 'Ultra super large light truck size for 37-inch wheels', 338),
('LT425/75R37', 'Mega super large light truck size for 37-inch wheels', 339),

-- 40-inch LT sizes
('LT335/75R40', 'Light truck size for 40-inch wheels', 340),
('LT345/75R40', 'Standard light truck size for 40-inch wheels', 341),
('LT355/75R40', 'Wider light truck size for 40-inch wheels', 342),
('LT365/75R40', 'Premium light truck size for 40-inch wheels', 343),
('LT375/75R40', 'Large light truck size for 40-inch wheels', 344),
('LT385/75R40', 'Extra large light truck size for 40-inch wheels', 345),
('LT395/75R40', 'Ultra large light truck size for 40-inch wheels', 346),
('LT405/75R40', 'Mega large light truck size for 40-inch wheels', 347),
('LT415/75R40', 'Super large light truck size for 40-inch wheels', 348),
('LT425/75R40', 'Ultra super large light truck size for 40-inch wheels', 349),
('LT435/75R40', 'Mega super large light truck size for 40-inch wheels', 350);

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
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    is_admin TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, first_name, last_name, password, email, is_admin) 
VALUES ('admin', 'Admin', 'User', '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'admin@gtautomotives.com', 1);

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