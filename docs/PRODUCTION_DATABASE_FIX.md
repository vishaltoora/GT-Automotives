# Production Database Migration Fix

## Problem Summary

Your production database is missing critical tables like `tires`, `brands`, and others. The `products.php` page queries the `tires` table, but it doesn't exist in production.

## Current Status

- **Production Tables:** `brands`, `inquiries`, `locations`, `sale_items`, `sales`, `services`, `tires`, `used_tire_photos`, `users`
- **Missing Tables:** `sizes`, `service_categories`, `database_migrations`
- **Critical Issue:** The `tires` table exists but may be empty or have structure issues

## Root Cause

The database migrations were not properly executed in production, leaving some tables missing or incomplete.

## Solution Steps

### Step 1: Check Current Database Status

Visit this URL to see what tables are missing:

```
https://your-domain.com/check_missing_tables.php
```

### Step 2: Run Database Migrations

Visit this URL to run all pending migrations:

```
https://your-domain.com/run_migrations_now.php
```

### Step 3: Alternative - Manual Schema Import

If migrations don't work, manually import the complete schema:

```bash
# Connect to your production database
mysql -u your_username -p your_database_name

# Import the complete schema
mysql -u your_username -p your_database_name < database/schema.sql
```

### Step 4: Verify the Fix

After running migrations, check these URLs:

1. **Products Page:** `https://your-domain.com/products.php`
2. **Admin Products:** `https://your-domain.com/admin/products.php`
3. **Database Status:** `https://your-domain.com/check_missing_tables.php`

## Expected Tables After Fix

Your database should have these tables:

- ✅ `users` - Admin users
- ✅ `brands` - Tire brands
- ✅ `sizes` - Tire sizes
- ✅ `tires` - Tire products (CRITICAL)
- ✅ `used_tire_photos` - Used tire images
- ✅ `inquiries` - Customer inquiries
- ✅ `sales` - Sales records
- ✅ `sale_items` - Sale line items
- ✅ `service_categories` - Service categories
- ✅ `services` - Available services
- ✅ `locations` - Store locations
- ✅ `database_migrations` - Migration tracking

## Quick Fix Commands

### Option 1: Run Migrations via Web Interface

```bash
curl -X POST https://your-domain.com/database/migrations.php -d 'run_migrations=1'
```

### Option 2: Direct Migration Script

```bash
curl https://your-domain.com/run_migrations_now.php
```

### Option 3: Manual Database Import

```bash
# Download schema file
wget https://your-domain.com/database/schema.sql

# Import to production database
mysql -u username -p database_name < schema.sql
```

## Verification Steps

### 1. Check Database Tables

```sql
MariaDB [gt_automotives]> SHOW TABLES;
```

Should show all expected tables.

### 2. Check Tires Table

```sql
MariaDB [gt_automotives]> SELECT COUNT(*) FROM tires;
```

Should return a number > 0.

### 3. Check Brands Table

```sql
MariaDB [gt_automotives]> SELECT * FROM brands LIMIT 5;
```

Should show brand data.

### 4. Test Products Page

Visit: `https://your-domain.com/products.php`

Should display tire products without errors.

## Troubleshooting

### If Migrations Fail

1. Check database permissions
2. Verify database connection
3. Check error logs
4. Try manual schema import

### If Tables Exist But No Data

1. Check if sample data was inserted
2. Run the sample data migration
3. Manually insert test data

### If Products Page Still Shows Errors

1. Check PHP error logs
2. Verify database connection settings
3. Test database queries directly

## Files Created for This Fix

1. **`check_missing_tables.php`** - Diagnoses missing tables
2. **`run_migrations_now.php`** - Runs migrations immediately
3. **`PRODUCTION_DATABASE_FIX.md`** - This guide

## Next Steps After Fix

1. **Test the products page** - Should show tire listings
2. **Test admin panel** - Should allow product management
3. **Add sample data** - If tables are empty
4. **Monitor for errors** - Check logs for any remaining issues

## Contact Information

If you need help with this fix:

- Check the migration results for specific errors
- Review the database connection settings
- Verify file permissions on the server

## Success Indicators

✅ All expected tables exist in database
✅ Products page loads without errors
✅ Admin panel can manage products
✅ Sample data is present in tables
✅ No PHP errors in logs
