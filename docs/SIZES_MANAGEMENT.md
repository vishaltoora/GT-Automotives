# Sizes Management Feature

This feature allows GT Automotives to manage tire sizes through a dedicated admin interface instead of using hardcoded arrays.

## Features Added

### 1. Database Schema

- **sizes table**: Stores tire sizes with metadata
  - `id`: Primary key
  - `name`: Size name (e.g., "205/55R16")
  - `description`: Optional description
  - `is_active`: Whether the size is available in dropdowns
  - `sort_order`: Order for display in lists
  - `created_at` / `updated_at`: Timestamps

### 2. Admin Management Pages

- **sizes.php**: List all sizes with edit/delete actions
- **add_size.php**: Add new tire sizes
- **edit_size.php**: Edit existing tire sizes
- **delete_size.php**: Delete sizes (with validation)

### 3. Integration Updates

- **add_product.php**: Now fetches sizes from database
- **edit_product.php**: Now fetches sizes from database
- **Admin navigation**: Added "Sizes" link in Inventory Management section

## File Structure

```
admin/
├── sizes.php (new - list sizes)
├── add_size.php (new - add size)
├── edit_size.php (new - edit size)
├── delete_size.php (new - delete size)
├── add_product.php (updated - uses database)
├── edit_product.php (updated - uses database)
└── includes/header.php (updated - added navigation)

database/
└── update_sizes_schema.php (new - migration script)
```

## Usage

### Accessing Sizes Management

1. Login to admin panel
2. Navigate to "Sizes" in the Inventory Management section
3. View, add, edit, or delete tire sizes

### Adding a New Size

1. Click "Add New Size" button
2. Enter size name (e.g., "205/55R16")
3. Add optional description
4. Set sort order (lower numbers appear first)
5. Choose active status
6. Save the size

### Editing a Size

1. Click "Edit" button next to any size
2. Modify the size details
3. Save changes

### Deleting a Size

1. Click "Delete" button next to any size
2. Confirm deletion
3. System will prevent deletion if size is used by products

## Database Migration

The system includes a migration script that:

1. Creates the `sizes` table
2. Inserts 16 default tire sizes
3. Creates indexes for performance
4. Verifies the setup

Run the migration with:

```bash
php database/update_sizes_schema.php
```

## Benefits

1. **Flexibility**: Add/remove sizes without code changes
2. **Organization**: Sort and categorize sizes
3. **Validation**: Prevent deletion of sizes in use
4. **Consistency**: All size selections use the same source
5. **Maintainability**: Centralized size management

## Default Sizes

The migration includes these default sizes:

- 205/55R16 (Standard passenger car size)
- 225/45R17 (Common performance tire size)
- 245/40R18 (Wide performance tire size)
- 265/35R19 (High-performance tire size)
- And 12 more common sizes...

## Security Features

- Admin authentication required for all operations
- Input validation and sanitization
- SQL injection prevention with prepared statements
- Confirmation dialogs for destructive actions
- Validation to prevent deletion of sizes in use
