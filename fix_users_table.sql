-- Fix Users Table - Add Missing Columns
-- Run this script in your MariaDB console to add first_name and last_name columns

USE gt_automotives;

-- Step 1: Add missing columns to users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(255) NOT NULL DEFAULT '',
ADD COLUMN last_name VARCHAR(255) NOT NULL DEFAULT '';

-- Step 2: Update existing users with default values
UPDATE users 
SET first_name = 'Admin', last_name = 'User' 
WHERE first_name = '' OR first_name IS NULL;

-- Step 3: Verify the changes
DESCRIBE users;

-- Step 4: Show updated users data
SELECT id, username, first_name, last_name, email, is_admin, created_at 
FROM users;

-- Step 5: Test adding a new user (optional)
-- INSERT INTO users (username, first_name, last_name, password, email, is_admin) 
-- VALUES ('testuser', 'Test', 'User', '$2y$10$Nq/VTTeC7NqIrdWUwJJvR.mRXMy8YH3wF5WKIUG63yzsCEP3Cq34q', 'test@example.com', 0); 