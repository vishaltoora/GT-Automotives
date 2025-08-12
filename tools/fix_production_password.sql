-- Fix Production Password for rohit.toora
-- Run this SQL script in your production database

-- Step 1: Check if user exists
SELECT id, username, first_name, last_name, email, is_admin 
FROM users 
WHERE username = 'rohit.toora';

-- Step 2: Update password to 'Maan1234'
-- This hash was generated for password 'Maan1234'
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'rohit.toora';

-- Step 3: Verify the update
SELECT id, username, first_name, last_name, email, is_admin 
FROM users 
WHERE username = 'rohit.toora';

-- Step 4: Alternative - Create user if it doesn't exist
-- Uncomment the following lines if the user doesn't exist:

/*
INSERT INTO users (username, first_name, last_name, password, email, is_admin) 
VALUES (
    'rohit.toora', 
    'Rohit', 
    'Toora', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'rohit.toora@gmail.com', 
    1
);
*/

-- Step 5: List all admin users
SELECT id, username, first_name, last_name, email, is_admin 
FROM users 
WHERE is_admin = 1; 