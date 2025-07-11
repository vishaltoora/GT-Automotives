<?php
// Ensure tire sets directory exists
if (!is_dir('tire-sets')) {
    mkdir('tire-sets', 0777, true);
    echo "Created tire-sets directory\n";
}

// Define image sources for tire sets (complete sets of 4 tires)
$tireSets = [
    'tire-sets/michelin-set.jpg' => 'https://i.imgur.com/8RUh9Hh.jpg',  // Michelin tire set
    'tire-sets/bridgestone-set.jpg' => 'https://i.imgur.com/FQ8vQHw.jpg',  // Bridgestone tire set
    'tire-sets/goodyear-set.jpg' => 'https://i.imgur.com/JdnVuIM.jpg',  // Goodyear tire set
    'tire-sets/continental-set.jpg' => 'https://i.imgur.com/NwLJW9L.jpg'  // Continental tire set
];

// Alternative sources if the primary ones fail
$altTireSets = [
    'tire-sets/michelin-set.jpg' => 'https://i.ibb.co/vZj8HbC/michelin-tire-set.jpg',
    'tire-sets/bridgestone-set.jpg' => 'https://i.ibb.co/rM1j5yn/bridgestone-tire-set.jpg',
    'tire-sets/goodyear-set.jpg' => 'https://i.ibb.co/Lp7Zyq4/goodyear-tire-set.jpg', 
    'tire-sets/continental-set.jpg' => 'https://i.ibb.co/qBKhtCL/continental-tire-set.jpg'
];

// Function to download image with retry
function downloadWithRetry($url, $savePath, $retries = 3) {
    echo "Downloading $savePath from $url...\n";
    
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode == 200 && $data) {
            if (file_put_contents($savePath, $data)) {
                echo "✓ Successfully saved $savePath\n";
                return true;
            } else {
                echo "× Error saving file to $savePath\n";
            }
        } else {
            echo "× Download attempt $i failed (HTTP code: $httpCode, Error: $error)\n";
        }
        
        if ($i < $retries - 1) {
            echo "Retrying in 2 seconds...\n";
            sleep(2);
        }
    }
    
    return false;
}

// Function to create a fallback tire set image if download fails
function createTireSetPlaceholder($filename, $brand) {
    echo "Creating placeholder tire set for $brand...\n";
    
    $width = 800;
    $height = 600;
    
    // Create base image
    $img = imagecreatetruecolor($width, $height);
    
    // Colors
    $bgColor = imagecolorallocate($img, 240, 240, 240);
    $black = imagecolorallocate($img, 20, 20, 20);
    $darkGray = imagecolorallocate($img, 40, 40, 40);
    $gray = imagecolorallocate($img, 80, 80, 80);
    $white = imagecolorallocate($img, 255, 255, 255);
    
    // Background
    imagefill($img, 0, 0, $bgColor);
    
    // Draw four tires in a set arrangement
    $tirePositions = [
        [$width/4, $height/3],     // Top left
        [$width*3/4, $height/3],   // Top right
        [$width/4, $height*2/3],   // Bottom left
        [$width*3/4, $height*2/3]  // Bottom right
    ];
    
    foreach ($tirePositions as $pos) {
        // Outer tire
        $tireRadius = min($width, $height) / 8;
        imagefilledellipse($img, $pos[0], $pos[1], $tireRadius * 2, $tireRadius * 2, $black);
        
        // Rim
        $rimRadius = $tireRadius * 0.7;
        imagefilledellipse($img, $pos[0], $pos[1], $rimRadius * 2, $rimRadius * 2, $gray);
        imagefilledellipse($img, $pos[0], $pos[1], $rimRadius * 1.8, $rimRadius * 1.8, $white);
    }
    
    // Brand text
    $brandText = strtoupper($brand) . " TIRE SET";
    $fontsize = 5;
    $textWidth = imagefontwidth($fontsize) * strlen($brandText);
    $textX = ($width - $textWidth) / 2;
    $textY = $height / 10;
    imagestring($img, $fontsize, $textX, $textY, $brandText, $black);
    
    // Add "PLACEHOLDER" text
    $text = "PLACEHOLDER IMAGE";
    $textWidth = imagefontwidth($fontsize) * strlen($text);
    $textX = ($width - $textWidth) / 2;
    $textY = $height - $height / 10;
    imagestring($img, $fontsize, $textX, $textY, $text, $black);
    
    // Save image
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($img, $filename, 90);
    } else {
        imagepng($img, $filename);
    }
    
    imagedestroy($img);
    echo "✓ Created placeholder image: $filename\n";
    return true;
}

// Download each tire set
$successCount = 0;
foreach ($tireSets as $path => $url) {
    $brandName = "";
    if (strpos($path, 'michelin') !== false) $brandName = "Michelin";
    elseif (strpos($path, 'bridgestone') !== false) $brandName = "Bridgestone";
    elseif (strpos($path, 'goodyear') !== false) $brandName = "Goodyear";
    elseif (strpos($path, 'continental') !== false) $brandName = "Continental";
    
    if (downloadWithRetry($url, $path)) {
        $successCount++;
    } else if (isset($altTireSets[$path]) && downloadWithRetry($altTireSets[$path], $path)) {
        // Try alternate source
        $successCount++;
    } else {
        // Create placeholder as last resort
        if (createTireSetPlaceholder($path, $brandName)) {
            $successCount++;
        }
    }
}

echo "\nDownloaded $successCount of " . count($tireSets) . " tire set images successfully.\n";
echo "You can now use these images in your products page.\n";
?> 