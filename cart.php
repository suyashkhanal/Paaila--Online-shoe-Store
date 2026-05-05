<?php
session_start();
include 'db.php';

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['uid'];

// Fetch cart items — now includes size column
$res = $conn->query("SELECT c.id, p.name, p.price, c.qty, c.size
                     FROM cart c
                     JOIN products p ON c.product_id = p.id
                     WHERE c.user_id = $uid");

$total      = 0;
$item_count = $res->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Paaila</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0; padding: 20px;
            display: flex; flex-direction: column; align-items: center;
        }
        .cart-wrapper { width: 100%; max-width: 480px; margin-top: 20px; }

        .nav-header { display: flex; align-items: center; margin-bottom: 20px; }
        .back-btn {
            text-decoration: none; color: #636e72;
            font-size: 0.9rem; display: flex; align-items: center;
        }
        .back-btn:hover { color: #2d3436; }
        h2 {
            color: #2d3436; margin: 0; font-weight: 700;
            flex-grow: 1; text-align: center; margin-right: 40px;
        }

        /* Product cards */
        .product-card {
            background: #fff; border-radius: 15px; padding: 18px 20px;
            margin-bottom: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid #edf2f7;
        }
        .product-info b  { display: block; font-size: 1rem; color: #2d3436; margin-bottom: 3px; }
        .product-info .size-tag {
            display: inline-block;
            background: #1a1c63; color: white;
            font-size: 0.75rem; font-weight: 700;
            padding: 3px 9px; border-radius: 20px;
            margin-bottom: 4px;
        }
        .product-info span { color: #636e72; font-size: 0.88rem; }

        .remove-link {
            color: #ff7675; text-decoration: none; font-size: 0.8rem;
            font-weight: bold; text-transform: uppercase;
            padding: 8px 12px; border-radius: 8px;
            background: #fff5f5; transition: 0.2s; white-space: nowrap;
        }
        .remove-link:hover { background: #ff7675; color: #fff; }

        /* Summary */
        .summary-card {
            background: #2d3436; color: #fff;
            border-radius: 15px; padding: 25px; margin-top: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .summary-line {
            display: flex; justify-content: space-between;
            margin-bottom: 10px; font-size: 0.9rem; opacity: 0.8;
        }
        .total-line {
            display: flex; justify-content: space-between;
            margin-top: 15px; padding-top: 15px;
            border-top: 1px solid #4b5557;
            font-size: 1.4rem; font-weight: bold;
        }
        .checkout-btn {
            display: block; background: #00b894; color: white;
            text-decoration: none; text-align: center;
            padding: 16px; border-radius: 10px; margin-top: 20px;
            font-weight: bold; font-size: 1.1rem;
            transition: transform 0.2s, background 0.2s;
        }
        .checkout-btn:hover { background: #00a082; transform: translateY(-2px); }

        .empty-state {
            text-align: center; background: white;
            padding: 50px; border-radius: 15px; color: #636e72;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Order history */
        .order-history-section { width: 100%; max-width: 480px; margin-top: 40px; }
        .order-history-section h3 {
            color: #2d3436; border-bottom: 2px solid #ddd;
            padding-bottom: 10px; margin-bottom: 15px;
        }
        .order-item {
            background: white; padding: 15px; border-radius: 12px;
            margin-bottom: 10px; border-left: 5px solid #dfe6e9;
            box-shadow: 0 2px 4px rgba(0,0,0,0.03);
        }
        .order-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 8px;
        }
        .order-id { font-weight: bold; color: #1a1c63; }
        .status-pill {
            font-size: 0.75rem; padding: 4px 10px;
            border-radius: 20px; font-weight: bold; text-transform: uppercase;
        }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .order-details { font-size: 0.85rem; color: #636e72; line-height: 1.6; }
    </style>
</head>
<body>

<div class="cart-wrapper">
    <div class="nav-header">
        <a href="index.php" class="back-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            &nbsp;Back
        </a>
        <h2>🛒 Shopping Cart</h2>
    </div>

    <?php if ($item_count > 0): ?>

        <?php while ($row = $res->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-info">
                    <b><?= htmlspecialchars($row['name']) ?></b>
                    <?php if (!empty($row['size'])): ?>
                        <span class="size-tag">UK <?= htmlspecialchars($row['size']) ?></span><br>
                    <?php endif; ?>
                    <span>Rs <?= number_format($row['price']) ?> × <?= $row['qty'] ?></span>
                </div>
                <a href="remove.php?id=<?= $row['id'] ?>" class="remove-link">Remove</a>
            </div>
            <?php $total += $row['price'] * $row['qty']; ?>
        <?php endwhile; ?>

        <div class="summary-card">
            <div class="summary-line"><span>Items:</span><span><?= $item_count ?></span></div>
            <div class="summary-line"><span>Shipping:</span><span>FREE</span></div>
            <div class="total-line">
                <span>Total:</span>
                <span>Rs <?= number_format($total) ?></span>
            </div>
            <a href="checkout.php" class="checkout-btn">Checkout Now →</a>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <p style="font-size:2rem;">🛒</p>
            <p>Your cart is empty.</p>
            <a href="index.php" style="color:#00b894; font-weight:bold; text-decoration:none;">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<!-- Order History -->
<div class="order-history-section">
    <h3>📦 My Orders</h3>
    <?php
    $orders_query = $conn->query("SELECT * FROM orders WHERE user_id = $uid ORDER BY id DESC");
    if ($orders_query && $orders_query->num_rows > 0):
        while ($order = $orders_query->fetch_assoc()):
            $status       = $order['status'];
            $status_class = "status-" . strtolower($status);
    ?>
        <div class="order-item">
            <div class="order-header">
                <span class="order-id">Order #<?= $order['id'] ?></span>
                <span class="status-pill <?= $status_class ?>"><?= $status ?></span>
            </div>
            <div class="order-details">
                <strong>Items:</strong> <?= htmlspecialchars($order['items']) ?><br>
                <strong>Address:</strong> <?= htmlspecialchars($order['address']) ?>
            </div>
        </div>
    <?php
        endwhile;
    else:
        echo "<p style='color:#999;font-size:0.9rem;'>No past orders found.</p>";
    endif;
    ?>
</div>

</body>
</html>