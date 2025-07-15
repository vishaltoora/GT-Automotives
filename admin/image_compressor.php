<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and autoloader
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../vendor/autoload.php';

// Require login
requireLogin();

// Set page title
$page_title = 'Image Compressor';

// Initialize compressor
$compressor = new \GTAutomotives\Utils\ImageCompressor('uploads/compressed/', 800, 600, 85);

$message = '';
$results = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $files = [];
    
    // Handle multiple file uploads
    foreach ($_FILES['images']['name'] as $key => $name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $files[] = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key]
            ];
        }
    }
    
    if (!empty($files)) {
        $results = $compressor->compressMultipleImages($files);
        $stats = $compressor->getCompressionStats($results);
        
        if (!empty($results)) {
            $message = "Successfully compressed " . count($results) . " images. Average compression: " . $stats['average_compression_ratio'] . "%";
        } else {
            $message = "No images were successfully compressed.";
        }
    } else {
        $message = "No valid images were uploaded.";
    }
}

// Include header
include_once 'includes/header.php';
?>

<div class="admin-header">
    <h1>Image Compressor</h1>
    <div class="admin-actions">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="admin-form">
    <h2>Upload Images for Compression</h2>
    <p>Select one or more images to compress. Maximum file size: 10MB per image.</p>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="images">Select Images:</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*" required>
            <small>Supported formats: JPEG, PNG, GIF, WebP</small>
        </div>
        
        <div class="form-submit">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-compress"></i> Compress Images
            </button>
        </div>
    </form>
</div>

<?php if (!empty($results)): ?>
    <div class="admin-form">
        <h2>Compression Results</h2>
        
        <div class="compression-stats">
            <?php
            $stats = $compressor->getCompressionStats($results);
            ?>
            <div class="stats-grid">
                <div class="stat-item">
                    <strong>Total Files:</strong> <?php echo $stats['total_files']; ?>
                </div>
                <div class="stat-item">
                    <strong>Original Size:</strong> <?php echo number_format($stats['total_original_size'] / 1024, 2); ?> KB
                </div>
                <div class="stat-item">
                    <strong>Compressed Size:</strong> <?php echo number_format($stats['total_compressed_size'] / 1024, 2); ?> KB
                </div>
                <div class="stat-item">
                    <strong>Space Saved:</strong> <?php echo number_format($stats['total_saved_bytes'] / 1024, 2); ?> KB
                </div>
                <div class="stat-item">
                    <strong>Average Compression:</strong> <?php echo $stats['average_compression_ratio']; ?>%
                </div>
            </div>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Original Filename</th>
                    <th>Compressed Filename</th>
                    <th>Original Size</th>
                    <th>Compressed Size</th>
                    <th>Compression %</th>
                    <th>Dimensions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['filename']); ?></td>
                        <td><?php echo htmlspecialchars($result['filename']); ?></td>
                        <td><?php echo number_format($result['original_size'] / 1024, 2); ?> KB</td>
                        <td><?php echo number_format($result['compressed_size'] / 1024, 2); ?> KB</td>
                        <td>
                            <span class="compression-badge compression-<?php echo $result['compression_ratio'] > 50 ? 'excellent' : ($result['compression_ratio'] > 30 ? 'good' : 'moderate'); ?>">
                                <?php echo $result['compression_ratio']; ?>%
                            </span>
                        </td>
                        <td><?php echo $result['new_dimensions']; ?></td>
                        <td>
                            <a href="<?php echo '../' . $result['filepath']; ?>" target="_blank" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="<?php echo '../' . $result['filepath']; ?>" download class="btn-action btn-edit">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
.compression-stats {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-item {
    background: white;
    padding: 1rem;
    border-radius: 6px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.compression-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.compression-excellent {
    background: #d4edda;
    color: #155724;
}

.compression-good {
    background: #fff3cd;
    color: #856404;
}

.compression-moderate {
    background: #f8d7da;
    color: #721c24;
}

.form-group input[type="file"] {
    padding: 0.5rem;
    border: 2px dashed #ddd;
    border-radius: 4px;
    background: #f8f9fa;
}

.form-group input[type="file"]:hover {
    border-color: #007bff;
    background: #e3f2fd;
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?> 