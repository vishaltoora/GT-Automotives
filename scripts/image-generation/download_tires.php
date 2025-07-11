<?php
// Make sure the tires directory exists
if (!is_dir('tires')) {
    mkdir('tires', 0777, true);
    echo "Created tires directory\n";
}

// Real tire images (high-quality)
$tireImages = [
    'tires/michelin-pilot-sport.jpg' => 'https://m.media-amazon.com/images/I/71R8o6d+WIL.jpg',
    'tires/bridgestone-potenza.jpg' => 'https://m.media-amazon.com/images/I/71VR1-Nf90L.jpg',
    'tires/goodyear-eagle.jpg' => 'https://m.media-amazon.com/images/I/71LZ6zc7IeL.jpg',
    'tires/continental-extremecontact.jpg' => 'https://m.media-amazon.com/images/I/81V-QqE9OBL.jpg'
];

// Background images (high-quality)
$backgroundImages = [
    'tire-installation-bg.jpg' => 'https://img.freepik.com/premium-photo/vehicle-tire-repair-workshop-mechanic-changing-car-wheel-auto-service_427859-273.jpg',
    'hero-bg.jpg' => 'https://img.freepik.com/premium-photo/mechanic-changed-tire-car-with-air-impact-wrench-car-lifted-by-hydraulic-floor-jack_52253-2431.jpg'
];

// Brand logo images
$logoImages = [
    'michelin-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c8/Michelin.svg/640px-Michelin.svg.png',
    'bridgestone-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/Bridgestone_logo.svg/640px-Bridgestone_logo.svg.png',
    'goodyear-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bb/Goodyear_logo.svg/640px-Goodyear_logo.svg.png',
    'continental-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/640px-Continental_AG_logo.svg.png'
];

// Function to download image with better error handling
function downloadWithRetry($url, $savePath, $retries = 3) {
    echo "Downloading $savePath...\n";
    
    // Create directory if needed
    $dir = dirname($savePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: $dir\n";
    }
    
    // Try to download
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
        curl_close($ch);
        
        if ($httpCode == 200 && $data) {
            if (file_put_contents($savePath, $data)) {
                echo "✓ Successfully saved $savePath\n";
                return true;
            } else {
                echo "× Error saving file to $savePath\n";
            }
        } else {
            echo "× Download attempt $i failed (HTTP code: $httpCode)\n";
        }
        
        // Wait before retry
        if ($i < $retries - 1) {
            echo "Retrying in 2 seconds...\n";
            sleep(2);
        }
    }
    
    // If we get here, all retries failed
    echo "✗ Failed to download $savePath after $retries attempts\n";
    
    // Create fallback placeholder
    createPlaceholder($savePath);
    return false;
}

// Create a placeholder image if download fails
function createPlaceholder($path) {
    echo "Creating placeholder for $path\n";
    
    $width = 500;
    $height = 400;
    if (strpos($path, 'logo') !== false) {
        $width = 300;
        $height = 150;
    }
    
    $img = imagecreatetruecolor($width, $height);
    $bgColor = imagecolorallocate($img, rand(100, 255), rand(100, 255), rand(100, 255));
    $textColor = imagecolorallocate($img, 0, 0, 0);
    imagefill($img, 0, 0, $bgColor);
    
    $text = basename($path, '.' . pathinfo($path, PATHINFO_EXTENSION));
    imagestring($img, 5, $width/10, $height/2, "Placeholder: $text", $textColor);
    
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext == 'jpg' || $ext == 'jpeg') {
        imagejpeg($img, $path, 90);
    } else {
        imagepng($img, $path);
    }
    imagedestroy($img);
    
    echo "✓ Created placeholder for $path\n";
}

// Download tire images
echo "Downloading tire images...\n";
foreach ($tireImages as $path => $url) {
    downloadWithRetry($url, $path);
}

// Download background images
echo "\nDownloading background images...\n";
foreach ($backgroundImages as $path => $url) {
    downloadWithRetry($url, $path);
}

// Download logo images
echo "\nDownloading logo images...\n";
foreach ($logoImages as $path => $url) {
    downloadWithRetry($url, $path);
}

echo "\nAll images have been processed. Please refresh your browser to see them!\n";
?> 