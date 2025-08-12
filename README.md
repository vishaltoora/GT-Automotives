# GT Automotives - Laravel Web Application

A modern, responsive web application for GT Automotives built with Laravel framework, featuring a complete tire shop management system.

## 🏗️ Architecture

This project follows **pure Laravel architecture** with:

- **MVC Pattern**: Models, Views, and Controllers properly separated
- **Eloquent ORM**: Database relationships and queries using Laravel's ORM
- **Blade Templates**: Clean, maintainable view templates
- **Route Management**: Organized routing with proper naming conventions
- **Form Validation**: Server-side validation with error handling
- **Responsive Design**: Mobile-first approach with modern CSS

## 🚀 Features

### Frontend

- **Home Page**: Hero section, features, and brand showcase
- **Products Page**: Product catalog with filtering and search
- **Contact Page**: Contact form with validation and business information
- **Responsive Design**: Works on all devices and screen sizes

### Admin Panel

- **Dashboard**: Overview of sales, inventory, and users
- **Sales Management**: Complete sales tracking and management
- **Inventory Management**: Product and stock management
- **User Management**: Admin user administration

## 📁 Project Structure

```
gt-automotives-web-page/
├── app/
│   ├── Http/Controllers/
│   │   ├── Frontend/
│   │   │   ├── HomeController.php
│   │   │   ├── ProductController.php
│   │   │   └── ContactController.php
│   │   └── Admin/
│   │       ├── AdminController.php
│   │       ├── SaleController.php
│   │       ├── UserController.php
│   │       └── InventoryController.php
│   └── Models/
│       ├── Product.php
│       ├── Brand.php
│       ├── Size.php
│       ├── Sale.php
│       ├── SaleItem.php
│       └── User.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── frontend/
│           ├── home.blade.php
│           ├── products.blade.php
│           └── contact.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── public/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
├── database/
│   └── seeders/
│       └── DatabaseSeeder.php
└── composer.json
```

## 🛠️ Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL/PostgreSQL
- Node.js (for asset compilation if needed)

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd gt-automotives-web-page
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Environment setup**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file with your database credentials:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=gt_automotives
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**

   ```bash
   php artisan migrate
   ```

6. **Seed the database**

   ```bash
   php artisan db:seed
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

## 🎯 Key Components

### Models

- **Product**: Tire products with brand and size relationships
- **Brand**: Tire manufacturers with logo and description
- **Size**: Tire sizes with categorization and sorting
- **Sale**: Sales transactions and management
- **User**: Admin user accounts

### Controllers

- **Frontend Controllers**: Handle public-facing pages
- **Admin Controllers**: Manage admin panel functionality
- **Proper separation of concerns** and RESTful design

### Views

- **Blade Templates**: Clean, maintainable HTML
- **Layout System**: Consistent design across pages
- **Component-based**: Reusable UI components
- **Responsive**: Mobile-first design approach

## 🔧 Configuration

### Database

The application uses Eloquent ORM with proper relationships:

- Products belong to Brands and Sizes
- Sales have multiple SaleItems
- Proper foreign key constraints

### Routes

- **Frontend Routes**: Public pages (home, products, contact)
- **Admin Routes**: Protected admin functionality
- **API Routes**: Future API endpoints
- **Named Routes**: Easy maintenance and updates

### Assets

- **CSS**: Modern, responsive styles with CSS Grid and Flexbox
- **JavaScript**: Vanilla JS with utility functions
- **Images**: Optimized for web performance

## 🚀 Deployment

### Production Setup

1. Set `APP_ENV=production` in `.env`
2. Configure production database
3. Set up web server (Apache/Nginx)
4. Configure caching and optimization

### Performance

- Database indexing on frequently queried fields
- Eager loading for relationships
- Pagination for large datasets
- Optimized asset delivery

## 📱 Responsive Design

The application is built with a mobile-first approach:

- **Mobile Navigation**: Hamburger menu for small screens
- **Flexible Grid**: CSS Grid and Flexbox for layouts
- **Touch-Friendly**: Optimized for mobile interactions
- **Progressive Enhancement**: Works on all devices

## 🔒 Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Protection**: Eloquent ORM prevents SQL injection
- **XSS Protection**: Blade templates escape output by default

## 🧪 Testing

### Database Seeding

The application includes sample data for testing:

- 5 major tire brands
- 10 common tire sizes
- 6 sample products

### Manual Testing

Test the following functionality:

- Product filtering and search
- Contact form submission
- Responsive design on different devices
- Admin panel access

## 📈 Future Enhancements

- **E-commerce Integration**: Shopping cart and checkout
- **Inventory API**: Real-time stock updates
- **Customer Portal**: Account management for customers
- **Advanced Search**: Elasticsearch integration
- **Multi-language Support**: Internationalization

## 🤝 Contributing

1. Follow Laravel coding standards
2. Use proper Git workflow
3. Test all changes thoroughly
4. Update documentation as needed

## 📄 License

This project is proprietary software for GT Automotives.

## 📞 Support

For technical support or questions:

- Email: gt-automotives@outlook.com
- Phone: (250) 986-9191

---

**Built with ❤️ using Laravel Framework**
