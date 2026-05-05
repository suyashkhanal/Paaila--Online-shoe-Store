<?php error_reporting(E_ALL); ini_set("display_errors", 1); ?>
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Paaila</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f7f6;
            display: flex; justify-content: center; padding: 30px 20px;
        }
        .checkout-card {
            background: white; padding: 30px; border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
            width: 100%; max-width: 420px;
        }
        h2 { margin-top: 0; color: #2d3436; text-align: center; }
        .form-group  { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #636e72; font-size: 0.9rem; }
        input, select {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 10px; font-size: 1rem;
        }
        #qr-section {
            display: none; text-align: center; margin-top: 15px; padding: 15px;
            border: 2px dashed #00b894; border-radius: 10px; background: #f0fffb;
        }
        .place-btn {
            width: 100%; background: #2ed573; color: white; border: none;
            padding: 15px; border-radius: 12px; font-weight: bold;
            font-size: 1.1rem; cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .place-btn:hover { background: #26af5f; }
        .back-link {
            display: block; text-align: center; margin-top: 15px;
            color: #888; text-decoration: none; font-size: 0.9rem;
        }

        /* Order summary box */
        .order-summary {
            background: #f8fafc; border-radius: 10px;
            padding: 15px; margin-bottom: 20px; border: 1px solid #e2e8f0;
        }
        .order-summary h4 { margin: 0 0 10px; color: #1a1c63; }
        .summary-item {
            display: flex; justify-content: space-between;
            font-size: 0.88rem; color: #555; padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-child { border-bottom: none; }
        .summary-item .size-badge {
            background: #1a1c63; color: white;
            font-size: 0.72rem; padding: 2px 7px;
            border-radius: 10px; margin-left: 6px;
        }
        .summary-total {
            display: flex; justify-content: space-between;
            font-weight: bold; font-size: 1rem; margin-top: 10px;
            padding-top: 10px; border-top: 2px solid #1a1c63; color: #1a1c63;
        }
    </style>
</head>
<body>
<div class="checkout-card">
    <h2>Complete Order</h2>

    <?php
    // Show order summary with sizes before the form
    $uid      = $_SESSION['uid'];
    $cart_res = $conn->query("SELECT c.product_id, p.name, p.price, c.qty, c.size
                               FROM cart c
                               JOIN products p ON c.product_id = p.id
                               WHERE c.user_id = $uid");

    $summary_items = [];
    $grand_total   = 0;

    if ($cart_res && $cart_res->num_rows > 0):
        while ($item = $cart_res->fetch_assoc()) {
            $summary_items[] = $item;
            $grand_total += $item['price'] * $item['qty'];
        }
    ?>
    <div class="order-summary">
        <h4>🛒 Your Order</h4>
        <?php foreach ($summary_items as $item): ?>
        <div class="summary-item">
            <span>
                <?= htmlspecialchars($item['name']) ?>
                <?php if (!empty($item['size'])): ?>
                    <span class="size-badge">UK <?= htmlspecialchars($item['size']) ?></span>
                <?php endif; ?>
                × <?= $item['qty'] ?>
            </span>
            <span>Rs <?= number_format($item['price'] * $item['qty']) ?></span>
        </div>
        <?php endforeach; ?>
        <div class="summary-total">
            <span>Total</span>
            <span>Rs <?= number_format($grand_total) ?></span>
        </div>
    </div>
    <?php else: ?>
        <p style="text-align:center;color:#999;">Your cart is empty. <a href="index.php">Shop now</a></p>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Delivery Address</label>
            <input type="text" name="address" placeholder="House no, Street, City" required>
        </div>
        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" name="phone" placeholder="e.g. 9800000000" required>
        </div>
        <div class="form-group">
            <label>Payment Method</label>
            <select name="payment_type" id="payment_type" onchange="toggleQR()" required>
                <option value="COD">Cash on Delivery</option>
                <option value="Online">Online Payment (QR Scan)</option>
            </select>
        </div>
        <div id="qr-section">
            <p style="margin:0;font-size:0.85rem;color:#00b894;font-weight:bold;">Scan to Pay Now</p>
            <img src="your-qr-code-image.png" alt="Payment QR" style="width:150px;margin-top:10px;">
            <p style="font-size:0.75rem;color:#636e72;margin-top:5px;">Screenshot or Ref ID if required.</p>
        </div>
        <button name="place" class="place-btn">✅ Confirm Order</button>
        <a href="cart.php" class="back-link">← Return to Cart</a>
    </form>

    <?php
    if (isset($_POST['place'])) {
        $addr     = mysqli_real_escape_string($conn, $_POST['address']);
        $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
        $pay_type = mysqli_real_escape_string($conn, $_POST['payment_type']);

        // Fetch cart again for processing — NOW includes size
        $cart_res2 = $conn->query("SELECT c.product_id, p.name, c.qty, c.size, p.quantity as stock
                                   FROM cart c
                                   JOIN products p ON c.product_id = p.id
                                   WHERE c.user_id = $uid");

        $items_ordered        = [];
        $update_stock_queries = [];

        while ($item = $cart_res2->fetch_assoc()) {
            $pid         = $item['product_id'];
            $bought_qty  = $item['qty'];
            $stock       = $item['stock'];
            $size        = $item['size'];

            // Build item string WITH size so admin can see it
            $size_label    = !empty($size) ? " [Size UK $size]" : "";
            $items_ordered[] = $item['name'] . $size_label . " (x$bought_qty)";

            // Stock check
            $new_stock = $stock - $bought_qty;
            if ($new_stock < 0) {
                echo "<script>alert('Not enough stock for {$item['name']}'); window.location='cart.php';</script>";
                exit;
            }
            $update_stock_queries[] = "UPDATE products SET quantity=$new_stock WHERE id=$pid";
        }

        $items_string = mysqli_real_escape_string($conn, implode(", ", $items_ordered));

        $q = "INSERT INTO orders (user_id, address, phone, payment_method, items, status)
              VALUES ('$uid', '$addr', '$phone', '$pay_type', '$items_string', 'Pending')";

        if ($conn->query($q)) {
            foreach ($update_stock_queries as $sql) {
                $conn->query($sql);
            }
            $conn->query("DELETE FROM cart WHERE user_id=$uid");
            echo "<script>alert('Order placed successfully!'); window.location='cart.php';</script>";
        }
    }
    ?>
</div>

<script>
function toggleQR() {
    var type = document.getElementById("payment_type").value;
    document.getElementById("qr-section").style.display = type === "Online" ? "block" : "none";
}
</script>
</body>
</html>