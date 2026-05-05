<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit;
}
include 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paaila - Online Shoe Store</title>
    <!-- BUG FIX: Removed stray <a><img> tag that was inside <head> causing broken page layout -->
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <style>
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: linear-gradient(135deg, #16225b, #351158);
            border-bottom: 1px solid #ddd;
            color: white;
        }
        .nav-container { display: flex; align-items: center; }
        .nav-links { margin-right: 20px; }
        .nav-links a {
            text-decoration: none; color: white;
            margin: 0 10px; font-size: 18px;
        }
        .nav-links a:hover { text-decoration: underline; }
        .search-box { display: flex; }
        .search-box input {
            padding: 8px 12px; border-radius: 4px 0 0 4px;
            border: none; outline: none;
        }
        .search-box button {
            padding: 8px 12px; border: none;
            background: #2ed573; color: white;
            border-radius: 0 4px 4px 0; cursor: pointer;
        }
        .products {
            display: flex; flex-wrap: wrap;
            gap: 20px; padding: 30px;
            justify-content: center;
        }
        .card {
            border: 1px solid #ddd; padding: 15px;
            border-radius: 10px; text-align: center;
            width: 200px; background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: transform 0.3s;
        }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; }
        .card h3 { margin: 10px 0 5px; font-size: 1rem; }
        .card p  { color: #555; margin: 0 0 10px; }
        .card a {
            display: inline-block; background: #333; color: white !important;
            padding: 8px 14px; border-radius: 5px;
            text-decoration: none !important; margin-top: 5px;
            transition: background 0.3s;
        }
        .card a:hover { background: #2ed573; }
        .herosection {
            text-align: center; padding: 40px 20px;
            background: linear-gradient(135deg, #16225b, #351158); color: white;
        }
        footer {
            text-align: center; padding: 15px;
            background: linear-gradient(135deg, #16225b, #351158); color: white;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<header>
    <img src="paaila.png" alt="Paaila Logo" style="width:120px; border-radius:16px; border:2px solid white; background:white;">
    <div class="nav-container">
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="cart.php">🛒 Cart</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username'] ?? '') ?>)</a>
        </nav>
        <form action="index.php" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search shoes..."
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Go</button>
        </form>
    </div>
</header>

<div class="herosection">
    <img src="paailahero (1).png" alt="Paaila Banner" style="width:80%; max-width:800px;">
</div>

<div class="products">
<?php
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$query  = "SELECT * FROM products";
if ($search != '') {
    $query .= " WHERE name LIKE '%$search%'";
}

$res = $conn->query($query);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()):
?>
    <div class="card">
        <a href="product.php?id=<?= $row['id'] ?>">
            <img src="uploads/<?= rawurlencode($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
        </a>
        <h3><?= htmlspecialchars($row['name']) ?></h3>
        <p>Rs <?= number_format($row['price']) ?></p>
        <form action="addcart.php" method="POST" style="margin-top:8px;">
        <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
        <button type="submit" style="background:#333;color:white;border:none;padding:10px 15px;border-radius:5px;cursor:pointer;width:100%;font-size:0.9rem;transition:background 0.3s;" onmouseover="this.style.background='#2ed573'" onmouseout="this.style.background='#333'">Add to Cart</button>
      </form>
    </div>
<?php
    endwhile;
} else {
    echo "<p style='color:#999;'>No shoes found matching your search.</p>";
}
?>
</div>

<footer>
    <p>© Paaila Store</p>
</footer>

</body>
</html>