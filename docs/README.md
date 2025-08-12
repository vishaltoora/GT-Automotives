# GT Automotives - Premium Tire Shop Web Application

A comprehensive web application for GT Automotives, a premium tire shop offering tire sales, services, and inventory management.

## 🚗 Overview

GT Automotives is a full-featured web application designed for tire shops to manage their inventory, sales, services, and customer interactions. The application provides both customer-facing features and comprehensive admin management tools.

## ✨ Features

### Customer-Facing Features

- **Product Catalog**: Browse tires by brand, size, and search functionality
- **Product Details**: View detailed tire information with pricing and stock levels
- **Contact System**: Customer inquiry forms for product information
- **Responsive Design**: Mobile-friendly interface for all devices

### Admin Management Features

- **Inventory Management**: Add, edit, and manage tire products
- **Sales Management**: Create invoices, track sales, and generate reports
- **Brand Management**: Manage tire brands and their information
- **Size Management**: Comprehensive tire size database with admin interface
- **Location Management**: Manage multiple store locations
- **Service Management**: Track tire services and maintenance
- **User Management**: Admin user accounts and permissions
- **Used Tire Management**: Special handling for used tire inventory

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Image Processing**: Intervention Image (PHP library)
- **Dependencies**: Composer for PHP package management

## 📁 Project Structure

```
gt-automotives-web-page/
├── admin/                    # Admin panel files
│   ├── includes/            # Admin-specific includes
│   ├── index.php           # Admin dashboard
│   ├── products.php        # Product management
│   ├── sales.php           # Sales management
│   ├── sizes.php           # Tire size management
│   ├── brands.php          # Brand management
│   ├── locations.php       # Location management
│   └── services.php        # Service management
├── includes/                # Core application includes
│   ├── db_connect.php      # Database connection
│   ├── auth.php            # Authentication system
│   └── error_handler.php   # Error handling
├── database/               # Database files
│   ├── schema.sql          # Main database schema
│   ├── add_sizes_table.sql # Tire sizes data
│   └── init_db.php         # Database initialization
├── css/                    # Stylesheets
│   └── style.css           # Main application styles
├── images/                 # Image assets
│   ├── logo.png           # Company logo
│   ├── tires/             # Tire product images
│   └── used_tires/        # Used tire images
├── uploads/                # File uploads directory
├── scripts/                # Utility scripts
├── vendor/                 # Composer dependencies
├── index.php               # Main customer homepage
├── products.php            # Customer product catalog
├── contact.php             # Contact page
├── composer.json           # PHP dependencies
├── .env                    # Environment configuration
└── README.md              # This file
```

## 🗄️ Database Schema

### Core Tables

- **brands**: Tire manufacturer information
- **sizes**: Tire size database with descriptions
- **tires**: Main product inventory
- **sales**: Sales transactions and invoices
- **sale_items**: Individual items in sales
- **users**: Admin user accounts
- **locations**: Store location information
- **services**: Service offerings
- **service_categories**: Service categorization
- **inquiries**: Customer inquiries
- **used_tire_photos**: Used tire image management

## 🚀 Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL/MariaDB database
- Web server (Apache/Nginx)
- Composer (for dependencies)

### Setup Instructions

1. **Clone the repository**

   ```bash
   git clone https://github.com/vishaltoora/gt-automotives-web-page.git
   cd gt-automotives-web-page
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Configure database**

   - Create a MySQL database
   - Update database connection in `includes/db_connect.php`
   - Run the database schema:

   ```bash
   mysql -u username -p database_name < database/schema.sql
   ```

4. **Set up environment**

   - Copy `.env.example` to `.env` (if available)
   - Update configuration values in `.env`

5. **Configure web server**

   - Point document root to project directory
   - Ensure proper permissions for uploads directory

6. **Create admin user**
   ```bash
   php add_admin_user.php
   ```

## 🔧 Configuration

### Database Configuration

Update `includes/db_connect.php` with your database credentials:

```php
$host = 'localhost';
$username = 'your_username';
$password = 'your_password';
$database = 'gt_automotives';
```

### Environment Variables

Configure `.env` file for application settings:

```env
APP_NAME=GT Automotives
APP_ENV=production
DB_HOST=localhost
DB_NAME=gt_automotives
DB_USER=your_username
DB_PASS=your_password
```

## 👥 Admin Access

### Default Admin Credentials

- **Username**: admin
- **Password**: admin123

**⚠️ Important**: Change default password after first login!

### Admin Features

- **Dashboard**: Overview of sales, inventory, and key metrics
- **Products**: Manage tire inventory with images and descriptions
- **Sales**: Create invoices, track payments, generate reports
- **Sizes**: Manage tire size database with descriptions
- **Brands**: Manage tire manufacturer information
- **Locations**: Manage multiple store locations
- **Services**: Track tire services and maintenance
- **Users**: Manage admin user accounts

## 📊 Key Features

### Inventory Management

- Add/edit tire products with images
- Track stock quantities
- Manage used tire inventory
- Brand and size categorization

### Sales System

- Create detailed invoices
- Track payment methods
- Generate sales reports
- Customer information management

### Size Management

- Comprehensive tire size database
- Admin interface for size management
- Sort order and status control
- Size descriptions and categorization

### Used Tire Features

- Special handling for used tire inventory
- Multiple photo support per used tire
- Condition tracking
- Separate pricing structure

## 🔒 Security Features

- Admin authentication system
- SQL injection protection
- XSS prevention
- File upload security
- Session management

## 🎨 Design Features

- Responsive design for all devices
- Modern, clean interface
- Brand logo integration
- Professional styling
- User-friendly navigation

## 📱 Responsive Design

The application is fully responsive and works on:

- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🛠️ Development

### Local Development Setup

1. Use PHP's built-in server for development:

   ```bash
   php -S localhost:8000
   ```

2. Access the application at `http://localhost:8000`

### File Structure Guidelines

- Keep admin files in `/admin` directory
- Use includes for shared functionality
- Follow consistent naming conventions
- Maintain separation of concerns

## 📝 Documentation

Additional documentation files:

- `SIZES_MANAGEMENT.md` - Tire size management guide
- `USED_TIRES_FEATURE.md` - Used tire functionality
- `DEPLOYMENT_INSTRUCTIONS.md` - Deployment guide
- `MANUAL_DEPLOYMENT_GUIDE.md` - Manual deployment steps

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is proprietary software for GT Automotives.

## 📞 Support

For support or questions:

- **Phone**: (250) 986-9191
- **Phone**: (250) 565-1571
- **Email**: gt-automotives@outlook.com
- **Contact**: Johny
- **Contact**: Harjinder Gill

## 🔄 Version History

- **v1.0**: Initial release with core functionality
- **v1.1**: Added size management system
- **v1.2**: Enhanced used tire features
- **v1.3**: Improved admin interface and security

---

**GT Automotives** - Premium Tire Solutions
_Quality tires, exceptional service_
