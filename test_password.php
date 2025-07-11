<?php
// Test password verification with the updated hash
$password = 'admin123';
$hash = '$2y$12$1ufupyZ6HcpYVKYXM2XcoeQP1v2PaZkZyov7eTmvobsRodwHXxQ1q';

echo "Testing password verification:\n";
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verification result: " . (password_verify($password, $hash) ? 'TRUE' : 'FALSE') . "\n";

if (password_verify($password, $hash)) {
    echo "✅ Password verification successful!\n";
} else {
    echo "❌ Password verification failed!\n";
}

// Generate new hash for admin123
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "New hash for 'admin123': $new_hash\n";

// Test the new hash
echo "Verification with new hash: " . (password_verify('admin123', $new_hash) ? 'TRUE' : 'FALSE') . "\n";
?> 