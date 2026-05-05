<?php
// BUG FIX: Added session_start and login check
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}

include 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Only delete cart items belonging to the logged-in user (security fix)
    $uid = $_SESSION['uid'];
    $conn->query("DELETE FROM cart WHERE id=$id AND user_id=$uid");
}

header('Location: cart.php');
exit;
?>