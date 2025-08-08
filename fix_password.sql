-- Fix Password for rohit.toora
-- This script will set a simple, working password

USE gt_automotives;

-- Update password to a simple, working password
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'rohit.toora';

-- Verify the update
SELECT id, username, email, is_admin FROM users WHERE username = 'rohit.toora';

-- Test password verification (this should work)
-- The password is: password 