<?php
// admin/login.php
/*
  Copyright (c) 2026:
  vatofichor - Sebastian Mass     [>_<]
  & Assisted By Gemini Antigravity \|\
*/

require_once __DIR__ . '/auth.php';

$error = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (password_verify($password, $config['admin_password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Error: Invalid password. Authentication failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Course Explorer</title>
    <!-- Google Fonts for retro/premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-desktop: #1e222b;
            --bg-window: #2c313c;
            --bg-panel: #21252b;
            --border-light: #4c5262;
            --border-dark: #181a1f;
            --text-main: #abb2bf;
            --text-bright: #e06c75;
            --accent-blue: #61afef;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-desktop);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .window {
            width: 100%;
            max-width: 400px;
            background-color: var(--bg-window);
            border: 2px solid;
            border-color: var(--border-light) var(--border-dark) var(--border-dark) var(--border-light);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            border-radius: 6px;
            overflow: hidden;
        }
        .window-header {
            height: 44px;
            background-color: var(--bg-window);
            border-bottom: 2px solid var(--border-dark);
            display: flex;
            align-items: center;
            padding: 0 16px;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent-blue);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .window-body {
            padding: 30px 24px;
            background-color: var(--bg-panel);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
        }
        input[type="password"] {
            width: 100%;
            background-color: var(--bg-desktop);
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 12px;
            font-size: 14px;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.15s ease;
        }
        input[type="password"]:focus {
            border-color: var(--accent-blue);
        }
        .btn {
            width: 100%;
            background-color: var(--accent-blue);
            color: #1e222b;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .error-msg {
            color: var(--text-bright);
            font-size: 13px;
            margin-top: 16px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="window">
        <div class="window-header">🔒 Admin Portal Authentication</div>
        <div class="window-body">
            <form method="POST">
                <div class="form-group">
                    <label for="password">Enter Administrator Password</label>
                    <input type="password" id="password" name="password" required autofocus autocomplete="current-password">
                </div>
                <button type="submit" class="btn">Authenticate</button>
                <?php if ($error): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
