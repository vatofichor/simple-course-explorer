<?php
// admin/new_page.php
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
$isReady = false;
$aiGenerated = true;

// Scan sections
$sections = [];
if (is_dir($sourceDir)) {
    $items = scandir($sourceDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir($sourceDir . '/' . $item)) {
            $sections[] = $item;
        }
    }
}
sort($sections);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetSection = $_POST['section'] ?? '';
    $orderPrefix = $_POST['order_prefix'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $title = $_POST['title'] ?? '';
    $contributors = $_POST['contributors'] ?? '';
    $mdBody = $_POST['markdown_body'] ?? '';
    $isReady = isset($_POST['ready']);
    $aiGenerated = isset($_POST['ai_generated']);
    
    // Clean inputs
    $orderPrefix = preg_replace('/[^\d]/', '', $orderPrefix);
    $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '', $filename);
    
    if (empty($targetSection) || empty($orderPrefix) || empty($filename) || empty($title)) {
        $error = 'Error: Section, order prefix, filename, and page title are required.';
    } elseif (!in_array($targetSection, $sections)) {
        $error = 'Error: Invalid section chosen.';
    } else {
        $targetFile = $sourceDir . '/' . $targetSection . '/' . $orderPrefix . '_' . $filename . '.md';
        
        if (file_exists($targetFile)) {
            $error = 'Error: Lesson file already exists at: ' . htmlspecialchars($orderPrefix . '_' . $filename . '.md');
        } else {
            // Build raw Markdown content
            $mdContent = '';
            if ($isReady) {
                $mdContent .= "<!-- ready: true -->\n";
            } else {
                $mdContent .= "<!-- ready: false -->\n";
            }
            if (!$aiGenerated) {
                $mdContent .= "<!-- ai-generated: false -->\n";
            } else {
                $mdContent .= "<!-- ai-generated: true -->\n";
            }
            if (!empty($contributors)) {
                $mdContent .= "<!-- contributors: " . trim($contributors) . " -->\n";
            }
            $mdContent .= "# " . trim($title) . "\n\n" . trim($mdBody) . "\n";
            
            if (file_put_contents($targetFile, $mdContent) !== false) {
                // Trigger compilation
                $convertScript = $rootDir . '/dev/admin_scripts/convert.php';
                if (file_exists($convertScript)) {
                    $cmd = 'php ' . escapeshellarg($convertScript);
                    shell_exec($cmd);
                }
                header('Location: /admin/dashboard.php?rebuilt=1');
                exit;
            } else {
                $error = 'Error: Failed to write file to directory. Check permissions.';
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
    <title>Create Lesson Page - Course Explorer Admin</title>
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
            --accent-green: #98c379;
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
            max-width: 600px;
            max-height: 95vh;
            background-color: var(--bg-window);
            border: 2px solid;
            border-color: var(--border-light) var(--border-dark) var(--border-dark) var(--border-light);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
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
            flex-shrink: 0;
        }
        .window-body {
            padding: 24px;
            background-color: var(--bg-panel);
            overflow-y: auto;
            flex: 1;
        }
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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
        input[type="text"], select, textarea {
            width: 100%;
            background-color: var(--bg-desktop);
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 10px 12px;
            font-size: 14px;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.15s ease;
            font-family: inherit;
        }
        input[type="text"]:focus, select:focus, textarea:focus {
            border-color: var(--accent-blue);
        }
        textarea {
            height: 200px;
            resize: vertical;
            font-family: monospace;
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
        <div class="window-header">📄 Create New Course Lesson Page</div>
        <div class="window-body">
            <form method="POST">
                <div class="form-group">
                    <label for="section">Target Section</label>
                    <select id="section" name="section" required>
                        <option value="">-- Select Section --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?php echo htmlspecialchars($sec); ?>" <?php echo (isset($targetSection) && $targetSection === $sec) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(preg_replace('/^\d+[-_]/', '', $sec)) . " (" . htmlspecialchars($sec) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="order_prefix">Order Prefix (e.g. 01)</label>
                        <input type="text" id="order_prefix" name="order_prefix" placeholder="01" required pattern="\d+" value="<?php echo htmlspecialchars($orderPrefix ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="filename">Filename (e.g. Intro)</label>
                        <input type="text" id="filename" name="filename" placeholder="Intro" required pattern="[a-zA-Z0-9_\-]+" value="<?php echo htmlspecialchars($filename ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="title">Page Title (Heading 1)</label>
                    <input type="text" id="title" name="title" placeholder="Introduction to Nouns" required value="<?php echo htmlspecialchars($title ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="contributors">Reviewed Contributors (Optional - comma separated)</label>
                    <input type="text" id="contributors" name="contributors" placeholder="John Doe, Sarah Jenkins" value="<?php echo htmlspecialchars($contributors ?? ''); ?>">
                </div>
                
                <div class="form-group-checkbox" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="ai_generated" name="ai_generated" value="1" <?php echo $aiGenerated ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="ai_generated" style="margin-bottom: 0; cursor: pointer; font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--accent-blue);">🤖 AI-GENERATED CONTENT</label>
                </div>

                <div class="form-group-checkbox" style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="ready" name="ready" value="1" <?php echo $isReady ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="ready" style="margin-bottom: 0; cursor: pointer; font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--accent-green);">🚀 Ready / Published</label>
                </div>

                <div class="form-group">
                    <label for="markdown_body">Markdown Body</label>
                    <textarea id="markdown_body" name="markdown_body" placeholder="Write lesson content here in Markdown format..."><?php echo htmlspecialchars($mdBody ?? ''); ?></textarea>
                </div>
                <div class="btn-row">
                    <a href="/admin/dashboard.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">Create Lesson</button>
                </div>
                <?php if ($error): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
