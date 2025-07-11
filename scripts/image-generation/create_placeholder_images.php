<?php
// Create directories if they don't exist
if (!file_exists('images/tires')) {
    mkdir('images/tires', 0777, true);
}

// Function to create a placeholder image
function createPlaceholderImage($filename, $width, $height, $text, $bgColor = null, $textColor = null) {
    // Create image
    $img = imagecreatetruecolor($width, $height);
    
    // Set colors
    if ($bgColor === null) {
        $bgColor = imagecolorallocate($img, rand(100, 255), rand(100, 255), rand(100, 255));
    }
    if ($textColor === null) {
        $textColor = imagecolorallocate($img, 0, 0, 0);
    }
    
    // Fill background
    imagefill($img, 0, 0, $bgColor);
    
    // Add text
    $font = 5; // Built-in font size (1-5)
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($img, $font, $x, $y, $text, $textColor);
    
    // Save image based on file extension
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($img, $filename, 90);
    } else {
        imagepng($img, $filename);
    }
    
    imagedestroy($img);
    echo "$filename created successfully\n";
}

// Create logo placeholders
createPlaceholderImage('images/michelin-logo.png', 200, 100, 'Michelin');
createPlaceholderImage('images/bridgestone-logo.png', 200, 100, 'Bridgestone');
createPlaceholderImage('images/goodyear-logo.png', 200, 100, 'Goodyear');
createPlaceholderImage('images/continental-logo.png', 200, 100, 'Continental');

// Create background images
createPlaceholderImage('images/hero-bg.jpg', 1200, 600, 'Hero Background', 
    imagecolorallocate(imagecreatetruecolor(1, 1), 50, 50, 150), 
    imagecolorallocate(imagecreatetruecolor(1, 1), 255, 255, 255));

// Create tire installation background
createPlaceholderImage('images/tire-installation-bg.jpg', 1200, 600, 'Tire Installation', 
    imagecolorallocate(imagecreatetruecolor(1, 1), 40, 100, 40), 
    imagecolorallocate(imagecreatetruecolor(1, 1), 255, 255, 255));

// Create tire product images
createPlaceholderImage('images/tires/michelin-pilot-sport.jpg', 500, 400, 'Michelin Pilot Sport');
createPlaceholderImage('images/tires/bridgestone-potenza.jpg', 500, 400, 'Bridgestone Potenza');
createPlaceholderImage('images/tires/goodyear-eagle.jpg', 500, 400, 'Goodyear Eagle');
createPlaceholderImage('images/tires/continental-extremecontact.jpg', 500, 400, 'Continental ExtremeContact');

echo "All placeholder images created successfully!\n";
?> 