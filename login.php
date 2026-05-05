<?php
session_start();
include 'db.php';

// If already logged in, redirect appropriately
if (isset($_SESSION['uid'])) {
    header("Location: " . ($_SESSION['role'] == 'admin' ? 'admin.php' : 'index.php'));
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    // BUG FIX #1: $name was set from $_POST['username'] but SQL used undefined $username
    $name     = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($name == '' || $password == '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        if (!$user) {
            $error = "User not found!";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Incorrect password!";
        } else {
            // BUG FIX #3: Sessions were NEVER being set — this is why admin page kept redirecting back to login
            session_regenerate_id(true);
            $_SESSION['uid']      = $user['id'];
            $_SESSION['role']     = $user['role'];   // needs 'role' column — see note below
            $_SESSION['username'] = $user['username'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paaila Login</title>
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
        .logincard {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3), 0 10px 10px -5px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 320px;
            text-align: center;
        }
        .logincard h1 {
            margin-top: 0;
            margin-bottom: 2rem;
            color: #1a1a1a;
            font-weight: 800;
            letter-spacing: -1px;
            text-transform: uppercase;
        }
        input[type="text"], input[type="password"] {
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            outline: none;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #6b27ff;
            box-shadow: 0 0 0 3px rgba(107, 39, 255, 0.2);
        }
        button {
            margin-top: 10px;
            padding: 14px;
            border: none;
            background-color: #1a1a1a;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s;
        }
        button:hover {
            background-color: #000;
            transform: translateY(-1px);
        }

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
        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.88rem;
            text-align: left;
        }
        p {
            margin-top: 25px;
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
        }
        a.register-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 24px;
            color: white;
            text-decoration: none;
            background: linear-gradient(135deg, #6b27ff 0%, #4f46e5 100%);
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(107, 39, 255, 0.3);
            transition: all 0.3s ease;
        }
        a.register-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(107, 39, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="logincard">
        <img src="paaila.png" alt="Paaila Logo" style="width:150px; border-radius:20px;">
        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" autocomplete="off">
            <!-- Dummy fields to prevent browser autofill -->
            <input type="text" style="display:none">
            <input type="password" style="display:none">

            <input type="text"     name="username" placeholder="Username" autocomplete="off" required><br>
            <div class="password-wrapper">
                <input type="password" id="loginPw" name="password" placeholder="Password" autocomplete="new-password" required>
                <button type="button" class="toggle-pw" onclick="togglePw('loginPw', this)" title="Show/Hide password">👁️</button>
            </div>
            <button name="login" type="submit">Login</button>
        </form>
    </div>

    <p>Don't have an account?<br><br>
        <a href="register.php" class="register-link">Register here</a>
    </p>
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