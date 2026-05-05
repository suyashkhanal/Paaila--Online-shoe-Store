<?php
/**
 * Fix Admin Password — run this ONCE then delete it
 * Open: http://localhost/Paaila/fix_password.php
 */
include 'db.php';

// ── Set your desired admin username & password here ──
$username = "Admin";
$password = "Adminpass";
// ─────────────────────────────────────────────────────

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed, $username);
$stmt->execute();
$ok = $stmt->affected_rows > 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Password</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#0f172a;
               display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .box { background:white; padding:40px; border-radius:16px; text-align:center;
               max-width:380px; width:100%; box-shadow:0 20px 40px rgba(0,0,0,0.4); }
        h2   { color:#1a1c63; margin-top:0; }
        .ok  { background:#d1fae5; color:#065f46; padding:14px; border-radius:10px;
               font-size:1rem; margin:20px 0; }
        .err { background:#fee2e2; color:#dc2626; padding:14px; border-radius:10px;
               font-size:1rem; margin:20px 0; }
        .creds { background:#f8fafc; border:2px solid #e2e8f0; border-radius:10px;
                 padding:15px; text-align:left; margin:15px 0; }
        .creds p { margin:6px 0; }
        .creds span { font-weight:bold; color:#1a1c63; font-family:monospace; }
        .warn { background:#fff3cd; border:1px solid #ffc107; border-radius:8px;
                padding:12px; font-size:0.85rem; color:#856404; margin-top:15px; }
        a.btn { display:inline-block; margin-top:20px; padding:12px 28px;
                background:#1a1c63; color:white; border-radius:8px;
                text-decoration:none; font-weight:bold; }
        a.btn:hover { background:#2d3baa; }
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Password Fix</h2>

    <?php if ($ok): ?>
        <div class="ok">✅ Password updated & hashed successfully!</div>
        <div class="creds">
            <p>👤 Username: <span><?= htmlspecialchars($username) ?></span></p>
            <p>🔑 Password: <span><?= htmlspecialchars($password) ?></span></p>
            <p>🛡️ Role: <span>admin</span></p>
        </div>
    <?php else: ?>
        <div class="err">❌ User "<?= htmlspecialchars($username) ?>" not found in database.<br><br>
            Check the username spelling above in this file.</div>
    <?php endif; ?>

    <div class="warn">⚠️ <strong>Delete fix_password.php after use!</strong></div>
    <a href="login.php" class="btn">Go to Login →</a>
</div>
</body>
</html>