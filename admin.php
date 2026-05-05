<?php
session_start();
if (!isset($_SESSION['uid']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'db.php';

if (isset($_POST['update_status'])) {
    $order_id   = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $conn->query("UPDATE orders SET status='$new_status' WHERE id=$order_id");
    header("Location: admin.php#orders-section");
    exit;
}

if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin.php");
    exit;
}

if (isset($_POST['update'])) {
    $id          = intval($_POST['id']);
    $price       = floatval($_POST['price']);
    $quantity    = intval($_POST['quantity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $sizes       = mysqli_real_escape_string($conn, $_POST['sizes']);
    $conn->query("UPDATE products SET price=$price, quantity=$quantity, description='$description', sizes='$sizes' WHERE id=$id");
    header("Location: admin.php");
    exit;
}

if (isset($_POST['add'])) {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $price       = floatval($_POST['price']);
    $quantity    = intval($_POST['quantity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $sizes       = mysqli_real_escape_string($conn, $_POST['sizes']);

    $image_db_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {
        $image_db_name = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_db_name);
    }

    $conn->query("INSERT INTO products (name, price, quantity, image, description, sizes)
                  VALUES ('$name', $price, $quantity, '$image_db_name', '$description', '$sizes')");
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Paaila Shoe Store</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f4f4; margin: 0; }
        .navbar { background: linear-gradient(135deg, #16225b, #351158); color: #fff; padding: 15px; text-align: center; }
        .navbar h1 { margin: 0 0 8px; font-size: 1.5rem; }
        .navbar a { text-decoration: none; color: white; margin: 0 10px; font-weight: bold; }
        .navbar a:hover { text-decoration: underline; }
        h2 { text-align: center; margin-top: 40px; color: #1a1c63; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        #addForm { background: white; padding: 25px 30px; width: 60%; margin: 20px auto; border-radius: 12px; box-shadow: 0 0 12px rgba(0,0,0,0.1); }
        .form-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .form-row input, .form-row textarea { flex: 1; min-width: 140px; padding: 9px 12px; border-radius: 7px; border: 1px solid #ccc; font-size: 0.95rem; font-family: inherit; }
        .form-row textarea { resize: vertical; min-height: 70px; }
        .hint { font-size: 0.78rem; color: #888; margin: -8px 0 10px 2px; }
        .table-wrap { overflow-x: auto; margin: 20px auto; width: 95%; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        th, td { padding: 11px 10px; border: 1px solid #ddd; text-align: center; vertical-align: middle; }
        th { background: #1a1c63; color: white; white-space: nowrap; }
        .thumb { width: 55px; height: 55px; object-fit: cover; border-radius: 6px; }
        td textarea { width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.85rem; font-family: inherit; resize: vertical; min-height: 55px; }
        td input[type="text"] { width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.85rem; }
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        .pending   { 
            background: #fff3cd; color: #856404; }
        .completed { 
            background: #d4edda; color: #155724; }
        .cancelled { 
            background: #f8d7da; color: #721c24; }
        .btn-green { 
            padding: 9px 18px; 
            background: #2ed573; 
            color: white; 
            border: none; 
            border-radius: 7px; 
            cursor: pointer; 
            font-weight: 600; 
            font-size: 0.95rem; }
        .btn-green:hover { 
            background: #26af5f; }
        .btn-blue { 
            padding: 6px 12px; background: #1a1c63; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .btn-blue:hover { 
            background: #2d3baa; }
        .btn-red { 
            padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem; text-decoration: none; display: inline-block; margin-top: 6px; }
        .btn-red:hover { 
            background: #c0392b; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>Admin Dashboard</h1>
    <p>
        Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?> &nbsp;|&nbsp;
        <a href="index.php">Go to Store</a> &nbsp;|&nbsp;
        <a href="#inventory-section">Inventory</a> &nbsp;|&nbsp;
        <a href="#orders-section">Orders</a> &nbsp;|&nbsp;
        <a href="logout.php">Logout</a>
    </p>
</div>

<section id="inventory-section">
    <h2>Add New Product</h2>
    <form id="addForm" method="post" enctype="multipart/form-data">
        <div class="form-row">
            <input type="text"   name="name"     placeholder="Product Name e.g. Nike Air Max" required>
            <input type="number" step="0.01" name="price" placeholder="Price (Rs)" required>
            <input type="number" name="quantity"  placeholder="Qty in Stock" required>
        </div>
        <div class="form-row">
            <textarea name="description" placeholder="Product description e.g. Lightweight running shoe with Air cushioning..." required></textarea>
        </div>
        <div class="form-row">
            <input type="text" name="sizes" placeholder="Sizes (comma separated) e.g. 6,7,8,9,10,11" required>
        </div>
        <p class="hint">Tip: Enter sizes separated by commas — 6,7,8,9,10,11</p>
        <div class="form-row">
            <input type="file" name="image" accept="image/*" required>
        </div>
        <button name="add" type="submit" class="btn-green">Add Product</button>
    </form>

    <h2>Existing Products</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>ID</th><th>Image</th><th>Name</th><th>Price (Rs)</th>
                <th>Qty</th><th>Description</th><th>Sizes</th><th>Action</th>
            </tr>
            <?php
            $res = $conn->query("SELECT * FROM products");
            while ($row = $res->fetch_assoc()):
            ?>
            <tr>
                <form method="post">
                    <td><?= $row['id'] ?></td>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img src="uploads/<?= rawurlencode($row['image']) ?>" class="thumb" alt="img">
                        <?php else: ?>
                            <span style="color:#aaa;font-size:0.8rem;">No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>" style="width:80px;"></td>
                    <td><input type="number" name="quantity" value="<?= $row['quantity'] ?? 0 ?>" style="width:55px;"></td>
                    <td><textarea name="description"><?= htmlspecialchars($row['description'] ?? '') ?></textarea></td>
                    <td>
                        <input type="text" name="sizes" value="<?= htmlspecialchars($row['sizes'] ?? '6,7,8,9,10,11') ?>" style="width:120px;">
                        <div style="font-size:0.72rem;color:#888;margin-top:3px;">comma separated</div>
                    </td>
                    <td>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button name="update" type="submit" class="btn-blue">Update</button>
                        <a href="admin.php?remove=<?= $row['id'] ?>"
                           onclick="return confirm('Remove this product?')"
                           class="btn-red">Remove</a>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</section>

<hr style="margin:40px 0;">

<section id="orders-section">
    <h2>Customer Orders</h2>
    <div class="table-wrap">
        <table>
            <tr>
                <th>Order ID</th><th>Items Ordered</th><th>Address</th>
                <th>Phone</th><th>Payment</th><th>Status</th><th>Action</th>
            </tr>
            <?php
            $order_res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
            if ($order_res && $order_res->num_rows > 0):
                while ($order = $order_res->fetch_assoc()):
                    $status = $order['status'];
                    $status_class = strtolower($status);
            ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td style="text-align:left;font-size:0.9rem;"><?= htmlspecialchars($order['items']) ?></td>
                <td><?= htmlspecialchars($order['address']) ?></td>
                <td><?= htmlspecialchars($order['phone']) ?></td>
                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                <td><span class="badge <?= $status_class ?>"><?= $status ?></span></td>
                <td>
                    <form method="post" style="display:flex;gap:5px;justify-content:center;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="new_status" style="padding:5px;border-radius:5px;">
                            <option value="Pending"   <?= $status=='Pending'   ? 'selected':'' ?>>Pending</option>
                            <option value="Completed" <?= $status=='Completed' ? 'selected':'' ?>>Completed</option>
                            <option value="Cancelled" <?= $status=='Cancelled' ? 'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-blue">Save</button>
                    </form>
                </td>
            </tr>
            <?php
                endwhile;
            else:
                echo "<tr><td colspan='7' style='color:#999;padding:20px;'>No orders placed yet.</td></tr>";
            endif;
            ?>
        </table>
    </div>
</section>
<br><br>
</body>
</html>