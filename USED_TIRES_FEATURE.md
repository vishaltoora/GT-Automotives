# Used Tires Feature

This feature allows GT Automotives to sell both new and used tires with photo uploads for used tires.

## Features Added

### 1. Database Schema Updates

- Added `condition` field to `tires` table (new/used)
- Created `used_tire_photos` table for storing multiple photos per used tire
- Added `customer_business_name` field to `sales` table

### 2. Product Management

- **Add Product**: Now includes condition selection (New/Used)
- **Edit Product**: Can modify condition and manage photos for used tires
- **View Product**: Displays product details with photo gallery for used tires
- **Inventory Management**: Shows condition badges and filtering options

### 3. Photo Upload System

- Multiple photo uploads for used tires
- Photo preview during upload
- Photo management (add/remove) in edit mode
- Photo gallery with modal view
- File validation (JPG, PNG, GIF, max 5MB)

### 4. Sales Integration

- Product selection shows condition in dropdown
- Invoice generation includes condition information
- Condition badges in all relevant displays

## File Structure

```
admin/
├── add_product.php (updated with photo upload)
├── edit_product.php (updated with photo management)
├── view_product.php (new - product details with photos)
├── inventory.php (updated with condition filtering)
└── create_sale.php (updated to show conditions)

database/
├── schema.sql (updated schema)
└── update_used_tires_schema.php (migration script)

images/
├── tires/ (existing)
└── used_tires/
    └── photos/ (new - stores uploaded photos)
```

## Usage

### Adding a Used Tire

1. Go to Admin → Products → Add Product
2. Select "Used" condition
3. Fill in product details
4. Upload photos of the used tire
5. Save the product

### Managing Used Tire Photos

1. Edit an existing used tire product
2. View existing photos with delete options
3. Upload additional photos
4. Save changes

### Viewing Used Tire Details

1. Go to Inventory Management
2. Filter by "Used Tires"
3. Click "View" on any used tire
4. See product details and photo gallery

### Creating Sales with Used Tires

1. Create a new sale
2. Select products (condition shown in dropdown)
3. Complete the sale
4. Generate invoice (includes condition information)

## Technical Details

### Database Tables

- `tires.condition`: 'new' or 'used'
- `used_tire_photos`: Stores photo URLs and order for used tires
- Foreign key relationship maintains data integrity

### Photo Storage

- Photos stored in `images/used_tires/photos/`
- Unique filenames prevent conflicts
- File validation ensures security
- Responsive photo gallery with modal view

### UI/UX Features

- Condition badges with color coding
- Filter options in inventory
- Photo preview during upload
- Modal photo viewer
- Responsive design

## Security Features

- File type validation
- File size limits (5MB)
- Unique filename generation
- SQL injection prevention
- XSS protection

## Browser Compatibility

- Modern browsers with ES6 support
- Responsive design for mobile devices
- Fallback for older browsers

## Future Enhancements

- Bulk photo upload
- Photo editing capabilities
- Advanced filtering options
- Photo watermarking
