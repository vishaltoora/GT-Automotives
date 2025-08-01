<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

// Require login
requireLogin();

// Get sale ID from URL
$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$sale_id) {
    header('Location: sales.php');
    exit;
}

// Get sale details with creator info
$sale_query = "SELECT s.*, 
               CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as created_by_name,
               u.username as created_by_username,
               si.quantity, si.unit_price, si.total_price,
               t.name as tire_name, t.size, t.`condition`, b.name as brand_name,
               l.name as location_name
               FROM sales s
               LEFT JOIN users u ON s.created_by = u.id
               LEFT JOIN sale_items si ON s.id = si.sale_id
               LEFT JOIN tires t ON si.tire_id = t.id
               LEFT JOIN brands b ON t.brand_id = b.id
               LEFT JOIN locations l ON t.location_id = l.id
               WHERE s.id = ?";
$sale_stmt = $conn->prepare($sale_query);
$sale_stmt->bind_param("i", $sale_id);
$sale_stmt->execute();
$sale_result = $sale_stmt->get_result();
$sale = $sale_result->fetch_assoc();

if (!$sale) {
    header('Location: sales.php');
    exit;
}

// Get sale items with product and service details
$items_query = "SELECT si.*, 
                       t.name as tire_name, t.size, t.condition, b.name as brand_name,
                       s.name as service_name, sc.name as service_category
                FROM sale_items si 
                LEFT JOIN tires t ON si.tire_id = t.id 
                LEFT JOIN brands b ON t.brand_id = b.id 
                LEFT JOIN services s ON si.service_id = s.id 
                LEFT JOIN service_categories sc ON s.category = sc.name 
                WHERE si.sale_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $sale_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$sale_items = [];
while ($row = $items_result->fetch_assoc()) {
    $sale_items[] = $row;
}

// Set page title
$page_title = 'Invoice - ' . $sale['invoice_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - GT Automotives</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Invoice Styles */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.4;
            color: #333;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #243c55 0%, #1e3a5f 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .invoice-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .invoice-header p {
            margin: 0.25rem 0 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .invoice-content {
            padding: 1.5rem;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #eee;
        }
        
        .invoice-details h3,
        .customer-details h3 {
            margin: 0 0 0.75rem 0;
            color: #243c55;
            font-size: 1.1rem;
            border-bottom: 2px solid #243c55;
            padding-bottom: 0.25rem;
        }
        
        .invoice-details p,
        .customer-details p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .customer-details p strong {
            display: inline-block;
            min-width: 60px;
        }
        
        .invoice-number {
            font-size: 0.9rem;
            font-weight: bold;
            color: #007bff;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }
        
        .invoice-table th {
            background: #f8f9fa;
            padding: 0.5rem;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        .invoice-table td {
            padding: 0.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .invoice-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .product-name {
            font-weight: bold;
            color: #333;
        }
        
        .product-details {
            color: #666;
            font-size: 0.8rem;
        }
        
        .item-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .item-product {
            background: #007bff;
            color: white;
        }
        
        .item-service {
            background: #28a745;
            color: white;
        }
        
        .item-unknown {
            background: #6c757d;
            color: white;
        }
        
        .item-name {
            font-weight: bold;
            color: #333;
        }
        
        .item-details {
            color: #666;
            font-size: 0.8rem;
        }
        
        .condition-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .condition-new {
            background: #d4edda;
            color: #155724;
        }
        
        .condition-used {
            background: #fff3cd;
            color: #856404;
        }
        
        .totals-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .totals-grid {
            display: grid;
            gap: 0.25rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
            font-size: 0.9rem;
        }
        
        .total-row.grand-total {
            font-size: 1.1rem;
            font-weight: bold;
            color: #243c55;
            border-top: 2px solid #dee2e6;
            padding-top: 0.5rem;
            margin-top: 0.25rem;
        }
        
        .invoice-footer {
            background: #f8f9fa;
            padding: 1rem;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .footer-section h4 {
            color: #243c55;
            margin: 0 0 0.75rem 0;
            font-size: 1rem;
            border-bottom: 1px solid #243c55;
            padding-bottom: 0.25rem;
        }
        
        .footer-section p {
            margin: 0.25rem 0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .footer-section p i {
            width: 16px;
            margin-right: 0.5rem;
            color: #243c55;
        }
        
        .footer-bottom {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            text-align: center;
        }
        
        .footer-bottom p {
            margin: 0.25rem 0;
            color: #666;
            font-size: 0.85rem;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 1rem;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        /* Print Styles */
        @media print {
            @page {
                size: letter;
                margin: 0.5in;
            }
            
            body {
                background: white;
                padding: 0;
                margin: 0;
                font-size: 10pt;
                line-height: 1.2;
            }
            
            .print-actions {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                margin: 0;
            }
            
            .invoice-header {
                background: #243c55 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 1rem;
            }
            
            .invoice-header img {
                height: 70px !important;
                width: 70px !important;
                max-width: 70px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                border: 2px solid rgba(255,255,255,0.3) !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .invoice-header h1 {
                font-size: 1.5rem;
            }
            
            .invoice-header p {
                font-size: 0.9rem;
            }
            
            .invoice-content {
                padding: 1rem;
            }
            
            .invoice-info {
                gap: 1rem;
                margin-bottom: 1rem;
                padding-bottom: 1rem;
                grid-template-columns: 1fr 1fr;
            }
            
            .invoice-details h3,
            .customer-details h3 {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
            
            .invoice-details p,
            .customer-details p {
                font-size: 0.8rem;
                margin: 0.2rem 0;
                word-wrap: break-word;
                overflow-wrap: break-word;
                white-space: nowrap;
            }
            
            .invoice-number {
                font-size: 0.8rem !important;
                font-weight: bold !important;
                color: #007bff !important;
            }
            
            .customer-details p strong {
                display: inline-block;
                min-width: 50px;
            }
            
            .invoice-table {
                font-size: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .invoice-table th,
            .invoice-table td {
                padding: 0.3rem;
            }
            
            .totals-section {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .total-row {
                font-size: 0.8rem;
                padding: 0.2rem 0;
            }
            
            .total-row.grand-total {
                font-size: 1rem;
            }
            
            .invoice-footer {
                padding: 0.75rem;
            }
            
            .footer-content {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .footer-section h4 {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }
            
            .footer-section p {
                font-size: 0.75rem;
                margin: 0.2rem 0;
            }
            
            .footer-bottom {
                padding-top: 0.75rem;
            }
            
            .footer-bottom p {
                font-size: 0.75rem;
                margin: 0.2rem 0;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .invoice-header img {
                height: 60px !important;
                width: 60px !important;
                max-width: 60px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                border: 2px solid rgba(255,255,255,0.3) !important;
            }
            
            .invoice-header h1 {
                font-size: 1.5rem !important;
            }
            
            .invoice-header h3 {
                font-size: 1rem !important;
            }
            
            .invoice-table {
                font-size: 0.9rem;
            }
            
            .invoice-table th,
            .invoice-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <a href="view_sale.php?id=<?php echo $sale_id; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Sale
        </a>
    </div>

    <div class="invoice-container">
        <div class="invoice-header">
            <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                <img src="../images/logo.png" alt="GT Automotives Logo" style="height: 80px; width: 80px; max-width: 80px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);">
                <div>
                    <h1 style="margin: 0; font-size: 2rem; font-weight: bold;">GT Automotives</h1>
                    <h3 style="margin: 0.25rem 0 0 0; font-size: 1.1rem; opacity: 0.9;">16472991 Canada INC.</h3>
                </div>
            </div>
        </div>
        
        <div class="invoice-content">
            <div class="invoice-info">
                <div class="invoice-details">
                    <h3><i class="fas fa-receipt"></i> Invoice Details</h3>
                    <p><strong>Invoice Number:</strong> <span class="invoice-number"><?php echo htmlspecialchars($sale['invoice_number']); ?></span></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($sale['created_at'])); ?></p>
                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($sale['created_at'])); ?></p>
                </div>
                
                <div class="customer-details">
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($sale['customer_name']); ?></p>
                    <?php if ($sale['customer_business_name']): ?>
                        <p><strong>Business:</strong> <?php echo htmlspecialchars($sale['customer_business_name']); ?></p>
                    <?php endif; ?>
                    <?php if ($sale['customer_phone']): ?>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($sale['customer_phone']); ?></p>
                    <?php endif; ?>
                    <p><strong>Address:</strong> <?php echo $sale['customer_address'] ? htmlspecialchars($sale['customer_address']) : 'Prince George, BC'; ?></p>
                </div>
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Type</th>
                        <th>Item Details</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale_items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['tire_id']): ?>
                                    <span class="item-badge item-product">Product</span>
                                <?php elseif ($item['service_id']): ?>
                                    <span class="item-badge item-service">Service</span>
                                <?php else: ?>
                                    <span class="item-badge item-unknown">Unknown</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['tire_id']): ?>
                                    <div class="item-name"><?php echo htmlspecialchars($item['brand_name'] . ' - ' . $item['tire_name']); ?></div>
                                    <div class="item-details">Size: <?php echo htmlspecialchars($item['size']); ?> | Condition: <?php echo ucfirst($item['condition']); ?></div>
                                <?php elseif ($item['service_id']): ?>
                                    <div class="item-name"><?php echo htmlspecialchars($item['service_name']); ?></div>
                                    <div class="item-details">Category: <?php echo htmlspecialchars($item['service_category']); ?></div>
                                <?php else: ?>
                                    <div class="item-name">Unknown Item</div>
                                    <div class="item-details">No details available</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td><strong>$<?php echo number_format($item['total_price'], 2); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="totals-section">
                <div class="totals-grid">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($sale['subtotal'], 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>GST (<?php echo ($sale['gst_rate'] * 100); ?>%):</span>
                        <span>$<?php echo number_format($sale['gst_amount'], 2); ?></span>
                    </div>
                    <?php if ($sale['pst_rate'] > 0): ?>
                    <div class="total-row">
                        <span>PST (<?php echo ($sale['pst_rate'] * 100); ?>%):</span>
                        <span>$<?php echo number_format($sale['pst_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="total-row grand-total">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($sale['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($sale['notes']): ?>
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: #243c55; margin-bottom: 1rem;"><i class="fas fa-sticky-note"></i> Notes</h3>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($sale['notes'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="invoice-footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <p><strong>GT Automotives</strong></p>
 
                    <p><i class="fas fa-phone"></i> (250) 570-2333</p>
                    <p><i class="fas fa-envelope"></i> gt-automotives@outlook.com</p>
                    <!-- <p><i class="fas fa-globe"></i> www.gt-automotives.com</p> -->
                </div>
                
                <div class="footer-section">
                    <h4>Business Hours</h4>
                    <p><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</p>
                    <p><strong>Saturday:</strong> 9:00 AM - 5:00 PM</p>
                    <p><strong>Sunday:</strong> Closed</p>
                </div>
                
                <div class="footer-section">
                    <h4>Services</h4>
                    <p>• Tire Sales & Installation</p>
                    <p>• Tire Balancing</p>
                    <p>• Automotive's Repair Service</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <h4><strong>Thank you for choosing GT Automotives!</strong></h4>
              
            </div>
        </div>
    </div>

    <script>
        // Auto-print functionality (optional)
        // Uncomment the line below if you want the invoice to print automatically
        // window.onload = function() { window.print(); };
    </script>
</body>
</html> 