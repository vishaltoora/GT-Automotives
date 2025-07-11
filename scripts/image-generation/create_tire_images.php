<?php
// Make sure directories exist
if (!is_dir('tires')) {
    mkdir('tires', 0777, true);
    echo "Created tires directory\n";
}

// Function to create a realistic tire image
function createTireImage($filename, $width = 600, $height = 400, $brandName = '', $modelName = '') {
    echo "Creating tire image for $filename...\n";
    
    // Create base image
    $img = imagecreatetruecolor($width, $height);
    
    // Colors
    $black = imagecolorallocate($img, 20, 20, 20);
    $darkGray = imagecolorallocate($img, 40, 40, 40);
    $gray = imagecolorallocate($img, 80, 80, 80);
    $lightGray = imagecolorallocate($img, 200, 200, 200);
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Background (gradient)
    $bgColor = imagecolorallocate($img, 240, 240, 240);
    imagefill($img, 0, 0, $bgColor);
    
    // Draw tire outer circle
    $centerX = $width / 2;
    $centerY = $height / 2;
    $outerRadius = min($width, $height) * 0.4;
    imagefilledellipse($img, $centerX, $centerY, $outerRadius * 2, $outerRadius * 2, $black);
    
    // Draw tire inner circle (rim)
    $innerRadius = $outerRadius * 0.6;
    imagefilledellipse($img, $centerX, $centerY, $innerRadius * 2, $innerRadius * 2, $gray);
    imagefilledellipse($img, $centerX, $centerY, $innerRadius * 1.8, $innerRadius * 1.8, $lightGray);
    
    // Draw tire tread pattern
    $treadWidth = ($outerRadius - $innerRadius) * 0.7;
    $treadStart = $innerRadius + ($outerRadius - $innerRadius) * 0.15;
    
    // Draw radial tread lines
    for ($angle = 0; $angle < 360; $angle += 15) {
        $rad = deg2rad($angle);
        $x1 = $centerX + cos($rad) * $treadStart;
        $y1 = $centerY + sin($rad) * $treadStart;
        $x2 = $centerX + cos($rad) * ($treadStart + $treadWidth);
        $y2 = $centerY + sin($rad) * ($treadStart + $treadWidth);
        imagesetthickness($img, 3);
        imageline($img, $x1, $y1, $x2, $y2, $darkGray);
    }
    
    // Draw circular tread patterns
    for ($r = $treadStart; $r <= $treadStart + $treadWidth; $r += $treadWidth / 3) {
        imagesetthickness($img, 2);
        imagearc($img, $centerX, $centerY, $r * 2, $r * 2, 0, 360, $darkGray);
    }
    
    // Add brand name text
    $brandSize = 5; // font size (1-5)
    $brandTextWidth = imagefontwidth($brandSize) * strlen($brandName);
    $brandX = $centerX - ($brandTextWidth / 2);
    $brandY = $centerY - imagefontheight($brandSize) / 2;
    imagestring($img, $brandSize, $brandX, $brandY - 40, $brandName, $white);
    
    // Add model name text
    $modelSize = 4; // font size (1-5)
    $modelTextWidth = imagefontwidth($modelSize) * strlen($modelName);
    $modelX = $centerX - ($modelTextWidth / 2);
    $modelY = $centerY - imagefontheight($modelSize) / 2;
    imagestring($img, $modelSize, $modelX, $modelY + 40, $modelName, $white);
    
    // Save image
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($img, $filename, 90);
    } else {
        imagepng($img, $filename);
    }
    
    imagedestroy($img);
    echo "✓ Created tire image: $filename\n";
}

// Function to create a brand logo
function createBrandLogo($filename, $brandName) {
    echo "Creating logo for $brandName...\n";
    
    $width = 300;
    $height = 150;
    
    // Create base image with transparent background
    $img = imagecreatetruecolor($width, $height);
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $transparent);
    
    // Colors
    $black = imagecolorallocate($img, 0, 0, 0);
    $darkGray = imagecolorallocate($img, 50, 50, 50);
    $accent = imagecolorallocate($img, 0, 100, 200);
    
    // Brand-specific colors
    if (stripos($brandName, 'michelin') !== false) {
        $accent = imagecolorallocate($img, 0, 70, 170); // Michelin blue
    } elseif (stripos($brandName, 'bridgestone') !== false) {
        $accent = imagecolorallocate($img, 220, 0, 0); // Bridgestone red
    } elseif (stripos($brandName, 'goodyear') !== false) {
        $accent = imagecolorallocate($img, 255, 200, 0); // Goodyear yellow
    } elseif (stripos($brandName, 'continental') !== false) {
        $accent = imagecolorallocate($img, 255, 150, 0); // Continental orange
    }
    
    // Draw rounded rectangle as background
    $radius = 20;
    imagefilledrectangle($img, $radius, 0, $width - $radius, $height, $accent);
    imagefilledrectangle($img, 0, $radius, $width, $height - $radius, $accent);
    imagefilledellipse($img, $radius, $radius, $radius * 2, $radius * 2, $accent);
    imagefilledellipse($img, $width - $radius, $radius, $radius * 2, $radius * 2, $accent);
    imagefilledellipse($img, $radius, $height - $radius, $radius * 2, $radius * 2, $accent);
    imagefilledellipse($img, $width - $radius, $height - $radius, $radius * 2, $radius * 2, $accent);
    
    // Add brand name text
    $fontsize = 5;
    $textWidth = imagefontwidth($fontsize) * strlen($brandName);
    $textX = ($width - $textWidth) / 2;
    $textY = ($height - imagefontheight($fontsize)) / 2;
    imagestring($img, $fontsize, $textX, $textY, strtoupper($brandName), $black);
    
    // Save image
    imagepng($img, $filename);
    imagedestroy($img);
    
    echo "✓ Created logo: $filename\n";
}

// Function to create a service background image
function createServiceBackgroundImage($filename, $title) {
    echo "Creating service background: $filename...\n";
    
    $width = 1200;
    $height = 600;
    
    // Create base image
    $img = imagecreatetruecolor($width, $height);
    
    // Define colors
    $darkBlue = imagecolorallocate($img, 30, 50, 80);
    $lightBlue = imagecolorallocate($img, 50, 100, 160);
    $black = imagecolorallocate($img, 0, 0, 0);
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Fill with gradient-like background
    imagefill($img, 0, 0, $darkBlue);
    
    // Draw tire shop elements (simplified)
    
    // Floor
    imagefilledrectangle($img, 0, $height * 0.7, $width, $height, $black);
    
    // Draw some tire silhouettes on the side
    for ($i = 0; $i < 3; $i++) {
        $tireX = $width * 0.1 + ($i * $width * 0.15);
        $tireY = $height * 0.75;
        $tireSize = $height * 0.3;
        imagefilledellipse($img, $tireX, $tireY, $tireSize, $tireSize, $black);
        imagefilledellipse($img, $tireX, $tireY, $tireSize * 0.7, $tireSize * 0.7, $darkBlue);
    }
    
    // Draw a car lift or service area
    imagefilledrectangle($img, $width * 0.6, $height * 0.6, $width * 0.9, $height * 0.7, $lightBlue);
    
    // Add some tools
    for ($i = 0; $i < 5; $i++) {
        $toolX = $width * 0.5 + ($i * 30);
        $toolY = $height * 0.6;
        imagefilledrectangle($img, $toolX, $toolY, $toolX + 20, $toolY + 40, $lightBlue);
    }
    
    // Add title text
    $fontsize = 5;
    $text = strtoupper($title);
    $textWidth = imagefontwidth($fontsize) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = $height * 0.3;
    
    // Add text shadow
    imagestring($img, $fontsize, $textX + 2, $textY + 2, $text, $black);
    imagestring($img, $fontsize, $textX, $textY, $text, $white);
    
    // Save image
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($img, $filename, 90);
    } else {
        imagepng($img, $filename);
    }
    
    imagedestroy($img);
    echo "✓ Created background image: $filename\n";
}

// Create tire product images
createTireImage('tires/michelin-pilot-sport.jpg', 600, 400, 'MICHELIN', 'PILOT SPORT 4S');
createTireImage('tires/bridgestone-potenza.jpg', 600, 400, 'BRIDGESTONE', 'POTENZA RE980AS');
createTireImage('tires/goodyear-eagle.jpg', 600, 400, 'GOODYEAR', 'EAGLE F1 ASYMMETRIC');
createTireImage('tires/continental-extremecontact.jpg', 600, 400, 'CONTINENTAL', 'EXTREMECONTACT DWS06');

// Create brand logos
createBrandLogo('michelin-logo.png', 'Michelin');
createBrandLogo('bridgestone-logo.png', 'Bridgestone');
createBrandLogo('goodyear-logo.png', 'Goodyear');
createBrandLogo('continental-logo.png', 'Continental');

// Create background images
createServiceBackgroundImage('tire-installation-bg.jpg', 'PROFESSIONAL TIRE INSTALLATION');
createServiceBackgroundImage('hero-bg.jpg', 'GT AUTOMOTIVES');

echo "\nAll images have been created successfully!\n";
?> 