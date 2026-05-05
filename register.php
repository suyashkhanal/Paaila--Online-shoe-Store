<?php
include 'db.php';

$error   = '';
$success = '';

if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $password = trim($_POST['password']);

    if ($name == '' || $password == '') {
        $error = "Please fill in all fields.";
    } else {
        // Check if username already exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $name);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Username already taken. Please choose another.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $name, $hashed);
            if ($stmt->execute()) {
                $success = "Registration successful!";
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Paaila</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0f172a;
            background-image: radial-gradient(circle at 2px 2px, #1e293b 1px, transparent 0);
            background-size: 40px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 320px;
            text-align: center;
        }
        .card h2 { margin-top: 0; color: #1a1a1a; font-weight: 800; text-transform: uppercase; }
        input[type="text"], input[type="password"] {
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border: 2px solid #e2e8f0;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        input:focus { border-color: #6b27ff; box-shadow: 0 0 0 3px rgba(107,39,255,0.2); }
    
        .password-wrapper {
            position: relative;
            width: 100%;
            margin: 8px 0;
        }
        .password-wrapper input {
            width: 100%;
            margin: 0;
            padding-right: 45px;
        }
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: auto;
            margin: 0;
            color: #aaa;
            font-size: 1.1rem;
            line-height: 1;
            transition: color 0.2s;
        }
        .toggle-pw:hover {
            background: none;
            color: #6b27ff;
            transform: translateY(-50%);
        }
    button {
            margin-top: 10px; padding: 14px; border: none;
            background-color: #1a1a1a; color: white; border-radius: 8px;
            cursor: pointer; width: 100%; font-weight: 600; transition: transform 0.2s;
        }
        button:hover { background-color: #000; transform: translateY(-1px); }
        .error-msg  { background:#fee2e2; color:#dc2626; padding:10px; border-radius:8px; margin-bottom:12px; font-size:.88rem; }
        .success-msg{ background:#d1fae5; color:#065f46; padding:10px; border-radius:8px; margin-bottom:12px; font-size:.88rem; }
        p { margin-top: 20px; color: #cbd5e1; font-size: 14px; }
        a { color: #6b27ff; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <img src="paaila.png" alt="Paaila Logo" style="width:120px; border-radius:16px; margin-bottom:10px;">
        <h2>Register</h2>

        <?php if ($error):   ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success-msg"><?= $success ?> <a href="login.php">Login here →</a></div><?php endif; ?>

        <?php if (!$success): ?>
        <form method="post" autocomplete="off">
            <input type="text"     name="name"     placeholder="Username" autocomplete="off" required><br>
            <div class="password-wrapper">
                <input type="password" id="regPw" name="password" placeholder="Password" autocomplete="new-password" required>
                <button type="button" class="toggle-pw" onclick="togglePw('regPw', this)" title="Show/Hide password">👁️</button>
            </div>
            <button name="register" type="submit">Create Account</button>
        </form>
        <?php endif; ?>
    </div>
    <p>Already have an account? <a href="login.php">Login here</a></p>
<script>
function togglePw(inputId, btn) {
    var input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁️";
    }
}
</script>
</body>
</html>