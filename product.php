<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
include 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id   = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<p>Product not found. <a href='index.php'>Go back</a></p>";
    exit;
}

$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = array_map('trim', explode(',', $product['sizes']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Paaila</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; margin: 0; }
        .topbar { background: linear-gradient(135deg, #16225b, #351158); padding: 12px 20px; }
        .topbar a { color: white; text-decoration: none; font-weight: bold; font-size: 0.95rem; }
        .topbar a:hover { text-decoration: underline; }
        .page {
            max-width: 680px; margin: 35px auto; background: white;
            border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.09); overflow: hidden;
        }
        .product-img { width: 100%; max-height: 380px; object-fit: cover; display: block; }
        .no-img {
            width: 100%; height: 220px; background: #f0f0f0;
            display: flex; align-items: center; justify-content: center;
            color: #aaa; font-size: 1rem;
        }
        .info { padding: 28px 30px; }
        .info h2 { margin: 0 0 8px; color: #1a1c63; font-size: 1.6rem; }
        .price { font-size: 1.5rem; font-weight: bold; color: #2ed573; margin-bottom: 16px; }
        .description {
            color: #555; font-size: 0.97rem; line-height: 1.65; margin-bottom: 20px;
            padding: 14px; background: #f8fafc;
            border-left: 4px solid #1a1c63; border-radius: 0 8px 8px 0;
        }
        .stock { font-size: 0.92rem; font-weight: 600; margin-bottom: 20px; }
        .stock.in  { color: #2ed573; }
        .stock.out { color: #e74c3c; }
        .size-section { margin-bottom: 22px; }
        .size-section > label { display: block; font-weight: 700; color: #1a1c63; margin-bottom: 10px; font-size: 0.95rem; }
        .size-options { display: flex; flex-wrap: wrap; gap: 10px; }
        .size-options input[type="radio"] { display: none; }
        .size-options .size-label {
            padding: 9px 16px; border: 2px solid #ddd; border-radius: 8px;
            cursor: pointer; font-weight: 600; color: #555; transition: all 0.2s; display: inline-block;
        }
        .size-options input[type="radio"]:checked + .size-label {
            border-color: #1a1c63; background: #1a1c63; color: white;
        }
        .size-options .size-label:hover { border-color: #1a1c63; color: #1a1c63; }
        .add-btn {
            display: block; width: 100%; padding: 15px; background: #1a1c63;
            color: white; border: none; border-radius: 10px;
            font-size: 1.05rem; font-weight: bold; cursor: pointer; transition: background 0.3s;
        }
        .add-btn:hover { background: #2ed573; }
        .add-btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="topbar">
    <a href="index.php">← Back to Store</a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="cart.php">🛒 My Cart</a>
</div>

<div class="page">
    <?php if (!empty($product['image'])): ?>
        <img src="uploads/<?= rawurlencode($product['image']) ?>"
             class="product-img" alt="<?= htmlspecialchars($product['name']) ?>">
    <?php else: ?>
        <div class="no-img">No image available</div>
    <?php endif; ?>

    <div class="info">
        <h2><?= htmlspecialchars($product['name']) ?></h2>
        <div class="price">Rs <?= number_format($product['price']) ?></div>

        <?php if (!empty($product['description'])): ?>
        <div class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
        <?php endif; ?>

        <?php if (isset($product['quantity'])): ?>
        <div class="stock <?= $product['quantity'] > 0 ? 'in' : 'out' ?>">
            <?= $product['quantity'] > 0 ? "✅ In Stock ({$product['quantity']} pairs left)" : "❌ Out of Stock" ?>
        </div>
        <?php endif; ?>

        <!--
            BUG FIX: Use POST form to addcart.php
            Pass product id as hidden field, size as radio
            addcart.php reads $_POST['product_id'] and $_POST['size']
        -->
        <form action="addcart.php" method="POST">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

            <?php if (!empty($sizes)): ?>
            <div class="size-section">
                <label>Select Size (UK):</label>
                <div class="size-options">
                    <?php foreach ($sizes as $i => $s): ?>
                        <input type="radio"
                               name="size"
                               id="size_<?= $i ?>"
                               value="<?= htmlspecialchars($s) ?>"
                               <?= $i === 0 ? 'checked' : '' ?>>
                        <label class="size-label" for="size_<?= $i ?>"><?= htmlspecialchars($s) ?></label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="add-btn"
                <?= (isset($product['quantity']) && $product['quantity'] <= 0) ? 'disabled' : '' ?>>
                🛒 Add to Cart
            </button>
        </form>
    </div>
</div>
</body>
</html>