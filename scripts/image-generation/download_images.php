<?php
// Create directories if they don't exist
if (!file_exists('images')) {
    mkdir('images', 0777, true);
}
if (!file_exists('images/tires')) {
    mkdir('images/tires', 0777, true);
}

// List of image URLs to download
$images = [
    // Brand logos
    'michelin-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Michelin.svg/200px-Michelin.svg.png',
    'bridgestone-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Bridgestone_logo.svg/200px-Bridgestone_logo.svg.png',
    'goodyear-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/74/Goodyear_logo.svg/200px-Goodyear_logo.svg.png',
    'continental-logo.png' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/Continental_AG_logo.svg/200px-Continental_AG_logo.svg.png',
    
    // Backgrounds
    'hero-bg.jpg' => 'https://images.unsplash.com/photo-1486006095121-d23fdc398b32?q=80&w=1200&auto=format&fit=crop',
    'tire-installation-bg.jpg' => 'https://images.unsplash.com/photo-1621157609862-ffa9e4140348?q=80&w=1200&auto=format&fit=crop',
    
    // Tire products for the shop
    'tires/michelin-pilot-sport.jpg' => 'https://images.unsplash.com/photo-1621963294320-fd01bb3c24a8?q=80&w=500&auto=format&fit=crop',
    'tires/bridgestone-potenza.jpg' => 'https://images.unsplash.com/photo-1582070916757-6cf45209735e?q=80&w=500&auto=format&fit=crop',
    'tires/goodyear-eagle.jpg' => 'https://images.unsplash.com/photo-1527247043084-89192b48e357?q=80&w=500&auto=format&fit=crop',
    'tires/continental-extremecontact.jpg' => 'https://images.unsplash.com/photo-1536350583537-65f48d55b7ed?q=80&w=500&auto=format&fit=crop'
];

// Download each image
foreach ($images as $filename => $url) {
    echo "Downloading $filename from $url...\n";
    $imageData = @file_get_contents($url);
    
    if ($imageData === false) {
        echo "Failed to download $filename\n";
        // If download fails, create a colored placeholder
        $img = imagecreatetruecolor(300, 200);
        $bgColor = imagecolorallocate($img, rand(100, 255), rand(100, 255), rand(100, 255));
        imagefill($img, 0, 0, $bgColor);
        
        // Add text to the placeholder
        $textColor = imagecolorallocate($img, 0, 0, 0);
        $text = basename($filename, pathinfo($filename, PATHINFO_EXTENSION));
        imagestring($img, 5, 20, 90, $text, $textColor);
        
        // Save as PNG or JPG based on extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext == 'jpg' || $ext == 'jpeg') {
            imagejpeg($img, $filename, 90);
        } else {
            imagepng($img, $filename);
        }
        imagedestroy($img);
    } else {
        if (!file_put_contents($filename, $imageData)) {
            echo "Failed to save $filename\n";
        } else {
            echo "$filename saved successfully\n";
        }
    }
}

echo "All images processed!\n";
?> 