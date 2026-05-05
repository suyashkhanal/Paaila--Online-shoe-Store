<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
include 'db.php';

// BUG FIX: Now reads from POST (product.php sends POST form)
// product_id comes from hidden input, size from radio button
if (!isset($_POST['product_id'])) {
    header('Location: index.php');
    exit;
}

$product_id = intval($_POST['product_id']);
$user_id    = $_SESSION['uid'];
$size       = isset($_POST['size']) ? mysqli_real_escape_string($conn, trim($_POST['size'])) : '';

// Check if same product + same size already in cart
$res = $conn->query("SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id AND size='$size'");

if ($res->num_rows > 0) {
    // Same product + same size → increase qty
    $conn->query("UPDATE cart SET qty = qty + 1 WHERE user_id=$user_id AND product_id=$product_id AND size='$size'");
} else {
    // New entry
    $conn->query("INSERT INTO cart (user_id, product_id, qty, size) VALUES ($user_id, $product_id, 1, '$size')");
}

// Also handle quick Add to Cart from index.php (GET with ?id=)
// Redirect back to where user came from
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
header("Location: " . $redirect);
exit;
?>