<?php
// Create directories if they don't exist
if (!file_exists('images/tires')) {
    mkdir('images/tires', 0777, true);
}

// Function to download an image using cURL
function downloadImage($url, $saveTo) {
    echo "Downloading $saveTo from $url...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode == 200 && !curl_errno($ch) && $data) {
        file_put_contents($saveTo, $data);
        echo "✓ Successfully saved $saveTo\n";
        return true;
    } else {
        echo "✗ Failed to download $saveTo (HTTP code: $httpCode, Error: " . curl_error($ch) . ")\n";
        return false;
    }
    
    curl_close($ch);
}

// List of actual tire images to download
$images = [
    // Tire logos (smaller files, higher chance of success)
    'michelin-logo.png' => 'https://1000logos.net/wp-content/uploads/2017/03/Michelin-logo-500x281.jpg',
    'bridgestone-logo.png' => 'https://1000logos.net/wp-content/uploads/2017/03/Bridgestone-logo-500x281.jpg',
    'goodyear-logo.png' => 'https://1000logos.net/wp-content/uploads/2017/03/Goodyear-logo-500x281.jpg',
    'continental-logo.png' => 'https://1000logos.net/wp-content/uploads/2021/04/Continental-logo-500x281.jpg',
    
    // Background images
    'tire-installation-bg.jpg' => 'https://img.freepik.com/free-photo/mechanic-changing-car-wheel-auto-repair-service_1303-26897.jpg',
    'hero-bg.jpg' => 'https://img.freepik.com/free-photo/mechanic-working-auto-repair-garage_1303-26869.jpg',
    
    // Tire product images
    'tires/michelin-pilot-sport.jpg' => 'https://img.freepik.com/free-photo/closeup-car-wheel-modern-black-tire-asphalt-road_158595-5157.jpg',
    'tires/bridgestone-potenza.jpg' => 'https://img.freepik.com/free-photo/car-wheel-close-up-isolated-black-background_185193-18510.jpg',
    'tires/goodyear-eagle.jpg' => 'https://img.freepik.com/free-photo/close-up-car-wheel-tie-red-sport-auto_1268-14419.jpg',
    'tires/continental-extremecontact.jpg' => 'https://img.freepik.com/free-photo/selective-focus-shot-new-black-rubber-tire_181624-20877.jpg'
];

// Try these alternative sources if the main ones fail
$alternateImages = [
    'michelin-logo.png' => 'https://logowik.com/content/uploads/images/michelin1183.jpg',
    'bridgestone-logo.png' => 'https://logowik.com/content/uploads/images/bridgestone6153.jpg',
    'goodyear-logo.png' => 'https://logowik.com/content/uploads/images/goodyear-tire-and-rubber-company5827.jpg',
    'continental-logo.png' => 'https://logowik.com/content/uploads/images/continental1402.jpg',
    
    'tire-installation-bg.jpg' => 'https://img.freepik.com/free-photo/car-service-worker-replacing-tire-vehicle_1303-26926.jpg',
    'hero-bg.jpg' => 'https://img.freepik.com/free-photo/mechanic-works-with-tire-service-station_1157-30194.jpg',
    
    'tires/michelin-pilot-sport.jpg' => 'https://img.freepik.com/free-photo/cars-modern-new-showroom-car-dealer_1303-15538.jpg',
    'tires/bridgestone-potenza.jpg' => 'https://img.freepik.com/free-photo/close-up-car-wheel-tire-automobile-vehicle-transportation-concept_53876-146816.jpg',
    'tires/goodyear-eagle.jpg' => 'https://img.freepik.com/free-photo/car-wheel-close-up-asphalt-road_158595-5195.jpg',
    'tires/continental-extremecontact.jpg' => 'https://img.freepik.com/free-photo/mechanic-hands-are-going-remove-car-tires-order-replace-new-tires_1150-14279.jpg'
];

// Track success count
$successCount = 0;
$totalCount = count($images);

// Try primary sources first
foreach ($images as $filename => $url) {
    if (downloadImage($url, $filename)) {
        $successCount++;
    } else {
        // If primary source fails, try alternate
        if (isset($alternateImages[$filename])) {
            echo "Trying alternate source...\n";
            if (downloadImage($alternateImages[$filename], $filename)) {
                $successCount++;
            } else {
                // If both sources fail, create a placeholder
                echo "Creating placeholder for $filename...\n";
                
                // Create image
                $img = imagecreatetruecolor(500, 400);
                $bgColor = imagecolorallocate($img, rand(100, 255), rand(100, 255), rand(100, 255));
                $textColor = imagecolorallocate($img, 0, 0, 0);
                imagefill($img, 0, 0, $bgColor);
                
                // Add text
                $text = basename($filename, '.' . pathinfo($filename, PATHINFO_EXTENSION));
                imagestring($img, 5, 50, 200, "Placeholder: $text", $textColor);
                
                // Save image
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if ($ext == 'jpg' || $ext == 'jpeg') {
                    imagejpeg($img, $filename, 90);
                } else {
                    imagepng($img, $filename);
                }
                imagedestroy($img);
                
                echo "✓ Created placeholder for $filename\n";
                $successCount++;
            }
        }
    }
}

echo "\nCompleted: $successCount of $totalCount images processed.\n";
echo "You can now refresh your browser to see the updated images.\n";
?> 