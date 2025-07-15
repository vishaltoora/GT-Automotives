<?php

namespace GTAutomotives\Utils;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressor
{
    private $manager;
    private $uploadDir;
    private $maxWidth;
    private $maxHeight;
    private $quality;

    public function __construct($uploadDir = 'uploads/', $maxWidth = 800, $maxHeight = 600, $quality = 85)
    {
        $this->manager = new ImageManager(new Driver());
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
        $this->quality = $quality;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Compress and resize an uploaded image
     * 
     * @param array $file $_FILES array element
     * @param string $filename Optional custom filename
     * @return array|false Array with success status and file info, or false on failure
     */
    public function compressImage($file, $filename = null)
    {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                return false;
            }

            // Generate filename if not provided
            if (!$filename) {
                $filename = $this->generateFilename($file['name']);
            }

            // Create image instance
            $image = $this->manager->read($file['tmp_name']);

            // Get original dimensions
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            // Calculate new dimensions while maintaining aspect ratio
            $newDimensions = $this->calculateDimensions($originalWidth, $originalHeight);

            // Resize image
            $image->resize($newDimensions['width'], $newDimensions['height']);

            // Save compressed image
            $filepath = $this->uploadDir . $filename;
            $image->save($filepath, $this->quality);

            // Get file size
            $fileSize = filesize($filepath);
            $originalSize = $file['size'];

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'original_size' => $originalSize,
                'compressed_size' => $fileSize,
                'compression_ratio' => round((1 - ($fileSize / $originalSize)) * 100, 2),
                'original_dimensions' => $originalWidth . 'x' . $originalHeight,
                'new_dimensions' => $newDimensions['width'] . 'x' . $newDimensions['height']
            ];

        } catch (\Exception $e) {
            error_log("Image compression error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compress multiple images
     * 
     * @param array $files Array of $_FILES elements
     * @return array Array of compression results
     */
    public function compressMultipleImages($files)
    {
        $results = [];
        
        foreach ($files as $file) {
            $result = $this->compressImage($file);
            if ($result) {
                $results[] = $result;
            }
        }
        
        return $results;
    }

    /**
     * Create thumbnail from image
     * 
     * @param string $imagePath Path to source image
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @param string $suffix Suffix for thumbnail filename
     * @return string|false Thumbnail filepath or false on failure
     */
    public function createThumbnail($imagePath, $width = 150, $height = 150, $suffix = '_thumb')
    {
        try {
            if (!file_exists($imagePath)) {
                return false;
            }

            $image = $this->manager->read($imagePath);
            
            // Resize and crop to create square thumbnail
            $image->cover($width, $height);
            
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . $suffix . '.' . $pathInfo['extension'];
            
            $image->save($thumbnailPath, $this->quality);
            
            return $thumbnailPath;

        } catch (\Exception $e) {
            error_log("Thumbnail creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @return bool
     */
    private function validateFile($file)
    {
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return false;
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique filename
     * 
     * @param string $originalName Original filename
     * @return string
     */
    private function generateFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Calculate new dimensions while maintaining aspect ratio
     * 
     * @param int $width Original width
     * @param int $height Original height
     * @return array
     */
    private function calculateDimensions($width, $height)
    {
        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
        
        // Don't upscale if image is smaller than max dimensions
        if ($ratio > 1) {
            return ['width' => $width, 'height' => $height];
        }
        
        return [
            'width' => round($width * $ratio),
            'height' => round($height * $ratio)
        ];
    }

    /**
     * Get compression statistics
     * 
     * @param array $results Array of compression results
     * @return array
     */
    public function getCompressionStats($results)
    {
        if (empty($results)) {
            return [
                'total_files' => 0,
                'total_original_size' => 0,
                'total_compressed_size' => 0,
                'average_compression_ratio' => 0
            ];
        }

        $totalOriginal = 0;
        $totalCompressed = 0;
        $totalRatio = 0;

        foreach ($results as $result) {
            $totalOriginal += $result['original_size'];
            $totalCompressed += $result['compressed_size'];
            $totalRatio += $result['compression_ratio'];
        }

        return [
            'total_files' => count($results),
            'total_original_size' => $totalOriginal,
            'total_compressed_size' => $totalCompressed,
            'average_compression_ratio' => round($totalRatio / count($results), 2),
            'total_saved_bytes' => $totalOriginal - $totalCompressed
        ];
    }
} 