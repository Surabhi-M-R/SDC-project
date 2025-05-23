<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: dashboard.php');
    exit;
}

$order_id = $_GET['order_id'];

// Verify the order belongs to the user
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ? AND o.user_id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: dashboard.php');
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopMania! - Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .confirmation-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        .confirmation-icon {
            font-size: 70px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .confirmation-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #333;
        }

        .confirmation-header p {
            font-size: 16px;
            color: #666;
        }

        .order-details {
            text-align: left;
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 6px;
        }

        .order-details h3 {
            margin-bottom: 20px;
            color: #444;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }

        .order-total {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .order-details p {
            margin: 10px 0;
            font-size: 15px;
            color: #333;
        }

        .continue-shopping {
            display: inline-block;
            background-color: #f0c14b;
            border: 1px solid #a88734;
            padding: 12px 25px;
            border-radius: 5px;
            color: #111;
            text-decoration: none;
            font-size: 16px;
            margin-top: 30px;
        }

        .continue-shopping:hover {
            background-color: #ddb347;
        }
    </style>
</head>
<body>

    <div class="confirmation-container">
        <div class="confirmation-icon">✔️</div>

        <div class="confirmation-header">
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been received and is being processed.</p>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
        </div>

        <div class="order-details">
            <h3>Order Summary</h3>
            <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> (×<?php echo $item['quantity']; ?>)</span>
                    <span>₹<?php echo number_format($item['price_per_unit'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="order-total">
                <span>Total:</span>
                <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>

            <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        </div>

        <a href="dashboard.php" class="continue-shopping">Continue Shopping</a>
    </div>

</body>
</html>
