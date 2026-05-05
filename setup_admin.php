<?php
/**
 * PAAILA - One-Time Admin Setup Script
 * =====================================
 * 1. Upload this file to your project folder
 * 2. Open it in browser: http://localhost/Paaila/setup_admin.php
 * 3. DELETE this file after you're done (for security)
 */

include 'db.php';

// ============================================================
//  CHANGE THESE to your desired admin username and password
// ============================================================
$admin_username = "admin";
$admin_password = "admin123";
// ============================================================

$hashed = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->query("SELECT id FROM users WHERE name='$admin_username'");

if ($check->num_rows > 0) {
    // Update existing user's role to admin
    $conn->query("UPDATE users SET role='admin', password='$hashed' WHERE name='$admin_username'");
    $msg = "✅ User '$admin_username' updated to admin role!";
} else {
    // Insert new admin user
    $conn->query("INSERT INTO users (name, password, role) VALUES ('$admin_username', '$hashed', 'admin')");
    $msg = "✅ Admin account created successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Paaila Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f172a;
            display: flex; justify-content: center;
            align-items: center; height: 100vh; margin: 0;
        }
        .box {
            background: white; padding: 40px; border-radius: 16px;
            text-align: center; max-width: 400px; width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        h2 { color: #1a1c63; margin-top: 0; }
        .msg { font-size: 1.1rem; margin: 20px 0; color: #333; }
        .creds {
            background: #f0fdf4; border: 2px solid #2ed573;
            border-radius: 10px; padding: 15px; margin: 20px 0;
            text-align: left;
        }
        .creds p { margin: 5px 0; font-size: 1rem; }
        .creds span { font-weight: bold; color: #1a1c63; font-family: monospace; }
        .warning {
            background: #fff3cd; border: 1px solid #ffc107;
            border-radius: 8px; padding: 12px; margin-top: 15px;
            font-size: 0.88rem; color: #856404;
        }
        a.btn {
            display: inline-block; margin-top: 20px;
            padding: 12px 28px; background: #1a1c63; color: white;
            border-radius: 8px; text-decoration: none; font-weight: bold;
        }
        a.btn:hover { background: #2d3baa; }
    </style>
</head>
<body>
<div class="box">
    <h2>🛠️ Paaila Setup</h2>
    <div class="msg"><?= $msg ?></div>

    <div class="creds">
        <p>👤 Username: <span><?= htmlspecialchars($admin_username) ?></span></p>
        <p>🔑 Password: <span><?= htmlspecialchars($admin_password) ?></span></p>
        <p>🛡️ Role: <span>admin</span></p>
    </div>

    <div class="warning">
        ⚠️ <strong>Delete this file after use!</strong><br>
        Keeping setup_admin.php on your server is a security risk.
    </div>

    <a href="login.php" class="btn">Go to Login →</a>
</div>
</body>
</html>