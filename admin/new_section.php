<?php
// admin/new_section.php
/*
  Copyright (c) 2026:
  vatofichor - Sebastian Mass     [>_<]
  & Assisted By Gemini Antigravity \|\
*/

require_once __DIR__ . '/auth.php';
require_auth();

$rootDir = dirname(__DIR__);
$sourceDir = $rootDir . '/content_source';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderPrefix = $_POST['order_prefix'] ?? '';
    $folderName = $_POST['folder_name'] ?? '';
    
    // Clean inputs
    $orderPrefix = preg_replace('/[^\d]/', '', $orderPrefix);
    $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folderName);
    
    if (empty($orderPrefix) || empty($folderName)) {
        $error = 'Error: Both order prefix and folder name are required.';
    } else {
        // Form directory name
        $dirName = $orderPrefix . '_' . $folderName;
        $targetPath = $sourceDir . '/' . $dirName;
        
        if (is_dir($targetPath)) {
            $error = 'Error: Section directory already exists: ' . htmlspecialchars($dirName);
        } else {
            if (mkdir($targetPath, 0755, true)) {
                // Trigger compilation to rebuild manifest and folders
                $convertScript = $rootDir . '/dev/admin_scripts/convert.php';
                if (file_exists($convertScript)) {
                    $cmd = 'php ' . escapeshellarg($convertScript);
                    shell_exec($cmd);
                }
                header('Location: /admin/dashboard.php?rebuilt=1');
                exit;
            } else {
                $error = 'Error: Failed to create directory. Check write permissions.';
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
    <title>Create Section - Course Explorer Admin</title>
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
            max-width: 450px;
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
            padding: 24px;
            background-color: var(--bg-panel);
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
        }
        input[type="text"] {
            width: 100%;
            background-color: var(--bg-desktop);
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 10px 12px;
            font-size: 14px;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.15s ease;
        }
        input[type="text"]:focus {
            border-color: var(--accent-blue);
        }
        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            border: none;
        }
        .btn-submit {
            background-color: var(--accent-blue);
            color: #1e222b;
        }
        .btn-cancel {
            background-color: var(--bg-desktop);
            border: 1px solid var(--border-light);
            color: var(--text-main);
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
        <div class="window-header">📁 Create New Course Section</div>
        <div class="window-body">
            <form method="POST">
                <div class="form-group">
                    <label for="order_prefix">Order Prefix (e.g. 03)</label>
                    <input type="text" id="order_prefix" name="order_prefix" placeholder="03" required pattern="\d+" title="Digits only.">
                </div>
                <div class="form-group">
                    <label for="folder_name">Folder Name (e.g. Grammar)</label>
                    <input type="text" id="folder_name" name="folder_name" placeholder="Grammar" required pattern="[a-zA-Z0-9_\-]+" title="Alphanumeric, dashes, or underscores only.">
                </div>
                <div class="btn-row">
                    <a href="/admin/dashboard.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">Create Section</button>
                </div>
                <?php if ($error): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
