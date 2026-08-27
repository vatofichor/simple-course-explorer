<?php
// admin/edit_page.php
/*
  Copyright (c) 2026:
  vatofichor - Sebastian Mass     [>_<]
  & Assisted By Gemini Antigravity \|\
*/

require_once __DIR__ . '/auth.php';
require_auth();

$rootDir = dirname(__DIR__);
$sourceDir = $rootDir . '/content_source';
$publicContentDir = $rootDir . '/public/content';
$error = '';
$success = '';

$file = $_GET['file'] ?? '';
// Clean file path to prevent traversal
$file = str_replace(array('..', '\\'), array('', '/'), $file);
$file = ltrim($file, '/');

$mdFile = $sourceDir . '/' . preg_replace('/\.html$/i', '.md', $file);

if (empty($file) || !file_exists($mdFile)) {
    die("Error: Source Markdown file not found.");
}

$sectionDir = dirname($mdFile);

// Handle AJAX Asset Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_upload']) && $_POST['ajax_upload'] === '1') {
    header('Content-Type: application/json');
    if (isset($_FILES['asset_file']) && $_FILES['asset_file']['error'] === UPLOAD_ERR_OK) {
        $uploadedFile = $_FILES['asset_file'];
        $origName = basename($uploadedFile['name']);
        
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $origName);
        $ext = strtolower(pathinfo($sanitizedName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'pdf', 'mp3', 'mp4'];
        
        if (!in_array($ext, $allowedExtensions)) {
            echo json_encode(['error' => 'File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions)]);
            exit;
        }
        
        $destFile = $sectionDir . '/' . $sanitizedName;
        if (move_uploaded_file($uploadedFile['tmp_name'], $destFile)) {
            // Trigger compilation
            $convertScript = $rootDir . '/dev/admin_scripts/convert.php';
            if (file_exists($convertScript)) {
                $cmd = 'php ' . escapeshellarg($convertScript);
                shell_exec($cmd);
            }
            
            // Re-scan assets
            $updatedAssets = [];
            if (is_dir($sectionDir)) {
                $items = scandir($sectionDir);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $sectionDir . '/' . $item;
                    if (is_file($path)) {
                        $itemExt = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                        if (in_array($itemExt, $allowedExtensions)) {
                            $updatedAssets[] = [
                                'name' => $item,
                                'size' => filesize($path),
                                'ext' => $itemExt
                            ];
                        }
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Asset "' . $sanitizedName . '" uploaded and compiled!',
                'assets' => $updatedAssets
            ]);
            exit;
        } else {
            echo json_encode(['error' => 'Failed to save uploaded asset.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'No file uploaded or upload error occurred.']);
        exit;
    }
}

// Scan current assets
$assets = [];
if (is_dir($sectionDir)) {
    $items = scandir($sectionDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $sectionDir . '/' . $item;
        if (is_file($path)) {
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'pdf', 'mp3', 'mp4'])) {
                $assets[] = [
                    'name' => $item,
                    'size' => filesize($path),
                    'ext' => $ext
                ];
            }
        }
    }
}

// Read markdown
$rawContent = file_get_contents($mdFile);

// Extract metadata tags
$contributors = '';
if (preg_match('/<!--\s*contributors:\s*(.+?)\s*-->/i', $rawContent, $matches)) {
    $contributors = trim($matches[1]);
    $rawContent = preg_replace('/<!--\s*contributors:\s*(.+?)\s*-->\s*/i', '', $rawContent);
}

$aiGenerated = true; // default
if (preg_match('/<!--\s*ai-generated:\s*(false|true)\s*-->\s*/i', $rawContent, $matches)) {
    $aiGenerated = (strtolower($matches[1]) === 'true');
    $rawContent = preg_replace('/<!--\s*ai-generated:\s*(false|true)\s*-->\s*/i', '', $rawContent);
}

$isReady = false; // default
if (preg_match('/<!--\s*ready:\s*(false|true)\s*-->\s*/i', $rawContent, $matches)) {
    $isReady = (strtolower($matches[1]) === 'true');
    $rawContent = preg_replace('/<!--\s*ready:\s*(false|true)\s*-->\s*/i', '', $rawContent);
}

$title = '';
if (preg_match('/^\s*#\s+(.+)$/m', $rawContent, $matches)) {
    $title = trim($matches[1]);
    // Strip the H1 title line from editor textarea
    $rawContent = preg_replace('/^\s*#\s+(.+)$\s*/m', '', $rawContent, 1);
}
$markdownBody = trim($rawContent);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $contributors = $_POST['contributors'] ?? '';
    $aiGenerated = isset($_POST['ai_generated']);
    $isReady = isset($_POST['ready']);
    $markdownBody = $_POST['markdown_body'] ?? '';
    
    if (empty($title)) {
        $error = 'Error: Page title is required.';
    } else {
        // Build raw markdown content
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
        $mdContent .= "# " . trim($title) . "\n\n" . trim($markdownBody) . "\n";
        
        if (file_put_contents($mdFile, $mdContent) !== false) {
            // Trigger compile script
            $convertScript = $rootDir . '/dev/admin_scripts/convert.php';
            if (file_exists($convertScript)) {
                $cmd = 'php ' . escapeshellarg($convertScript);
                shell_exec($cmd);
            }
            $success = 'Success: Lesson saved and rebuilt successfully!';
        } else {
            $error = 'Error: Failed to save file.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson - Course Explorer Admin</title>
    <!-- Google Fonts for retro/premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Load the JS parsing engine natively -->
    <script src="/lib/md2web-plugin/md2web.js"></script>
    
    <style>
        :root {
            --bg-desktop: #1e222b;
            --bg-window: #2c313c;
            --bg-panel: #21252b;
            --bg-content: #1e1e24;
            --border-light: #4c5262;
            --border-dark: #181a1f;
            --text-main: #abb2bf;
            --text-bright: #e06c75;
            --text-muted: #5c6370;
            --accent-green: #98c379;
            --accent-blue: #61afef;
            --accent-yellow: #e5c07b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-desktop);
            color: var(--text-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
        }

        /* OS Window Container */
        .window {
            width: 100%;
            height: 100%;
            background-color: var(--bg-window);
            border: 2px solid;
            border-color: var(--border-light) var(--border-dark) var(--border-dark) var(--border-light);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
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
            user-select: none;
            gap: 12px;
            flex-shrink: 0;
        }

        .window-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--accent-blue);
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .header-spacer { flex: 1; }

        .btn-header {
            background: none;
            border: 1px solid var(--border-light);
            color: var(--text-main);
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-header:hover {
            background-color: var(--bg-panel);
            color: #fff;
            border-color: var(--accent-blue);
        }

        /* Split Editor Workspace */
        .editor-workspace {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            background-color: var(--bg-panel);
        }

        /* Left Edit Column */
        .pane-edit {
            border-right: 2px solid var(--border-dark);
            display: flex;
            flex-direction: column;
            padding: 20px;
            overflow-y: auto;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
        }

        input[type="text"], textarea {
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

        input[type="text"]:focus, textarea:focus {
            border-color: var(--accent-blue);
        }

        textarea.editor-textarea {
            flex: 1;
            min-height: 350px;
            font-family: 'Fira Code', monospace;
            font-size: 13.5px;
            line-height: 1.6;
            resize: none;
        }

        .btn-save {
            background-color: var(--accent-blue);
            color: #1e222b;
            border: none;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
        }

        .btn-save:hover {
            opacity: 0.9;
        }

        /* Right Live Preview Column */
        .pane-preview {
            background-color: var(--bg-content);
            overflow-y: auto;
            padding: 40px;
        }

        /* CSS Styling mapping for preview container to match viewer */
        article {
            max-width: 100%;
            margin: 0 auto;
            line-height: 1.7;
            font-size: 15.5px;
            color: var(--text-main);
        }

        article h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-top: 10px;
            margin-bottom: 16px;
            border-left: 4px solid var(--accent-blue);
            padding-left: 15px;
            line-height: 1.2;
        }

        article h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--accent-green);
            margin-top: 36px;
            margin-bottom: 16px;
            border-bottom: 1px dashed var(--border-light);
            padding-bottom: 6px;
        }

        article h3 {
            font-size: 17px;
            font-weight: 600;
            color: var(--accent-yellow);
            margin-top: 30px;
            margin-bottom: 16px;
        }

        article h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-bright);
            margin-top: 26px;
            margin-bottom: 12px;
        }

        article p {
            margin-bottom: 16px;
        }

        article table {
            border-collapse: collapse;
            width: 100%;
            margin: 25px 0;
            font-size: 14px;
        }

        article th, article td {
            border: 1px solid var(--border-light);
            padding: 10px 12px;
            text-align: left;
        }

        article th {
            background-color: var(--bg-panel);
            color: #fff;
            font-weight: 600;
        }

        article tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.02);
        }

        article blockquote {
            border-left: 4px solid var(--accent-blue);
            padding: 12px 20px;
            margin: 20px 0;
            background-color: rgba(97, 175, 239, 0.05);
            color: #d1d5db;
            font-style: italic;
            border-radius: 0 4px 4px 0;
        }

        article ul, article ol {
            margin-left: 25px;
            margin-bottom: 16px;
        }

        article li {
            margin-bottom: 6px;
        }

        article code {
            font-family: 'Fira Code', monospace;
            background-color: var(--bg-panel);
            color: var(--text-bright);
            padding: 2px 6px;
            font-size: 13.5px;
            border-radius: 3px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        article pre {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-light);
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 20px 0;
        }

        article pre code {
            background-color: transparent;
            padding: 0;
            border: none;
            color: var(--text-main);
            display: block;
        }

        .math {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            color: #a8ffb2;
            background-color: rgba(168, 255, 178, 0.08);
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 16px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .alert-success {
            background-color: rgba(152, 195, 121, 0.1);
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
        }
        .alert-error {
            background-color: rgba(224, 108, 117, 0.1);
            border: 1px solid var(--text-bright);
            color: var(--text-bright);
        }
    </style>
</head>
<body>
    <div class="window">
        <!-- Header -->
        <div class="window-header">
            <div class="window-title">📝 Editor: <?php echo htmlspecialchars(basename($file)); ?></div>
            <div class="header-spacer"></div>
            <a href="/public/admin/markdown-guide.html" target="_blank" class="btn-header">📖 Markdown Guide</a>
            <a href="/admin/dashboard.php" class="btn-header">🚪 Close & Exit</a>
        </div>

        <!-- split workspace -->
        <div class="editor-workspace">
            <!-- Left Pane Form -->
            <form class="pane-edit" method="POST">
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">Lesson Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Lesson Title" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="contributors">Contributors HIL Reviewers</label>
                    <input type="text" id="contributors" name="contributors" value="<?php echo htmlspecialchars($contributors); ?>" placeholder="e.g. John Jenkins, Sarah" autocomplete="off">
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
                    <label>Section Assets (Images/Media)</label>
                    <div class="assets-box" style="background-color: var(--bg-desktop); border: 1px solid var(--border-light); border-radius: 4px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Upload controls -->
                        <div class="upload-controls" style="display: flex; gap: 10px; margin-bottom: 8px;">
                            <input type="file" id="asset_file_input" accept=".png,.jpg,.jpeg,.gif,.webp,.svg,.pdf,.mp3,.mp4" style="display: none;">
                            <button type="button" id="btn_select_file" class="btn-tool" style="flex: 1; padding: 8px; text-align: center;">Choose Asset File...</button>
                            <button type="button" id="btn_upload_asset" class="btn-tool" style="background-color: var(--accent-green); color: #1e222b; border: none; padding: 8px 15px; font-weight: 700; cursor: not-allowed; opacity: 0.5;" disabled>Upload</button>
                        </div>
                        <div id="upload_status" style="font-size: 11px; color: var(--text-muted);">No file selected</div>
                        
                        <!-- Assets List -->
                        <div id="assets_list" style="max-height: 120px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px; margin-top: 5px; border-top: 1px solid var(--border-light); padding-top: 8px;">
                            <?php if (empty($assets)): ?>
                                <div id="no_assets_text" style="font-size: 12px; color: var(--text-muted); font-style: italic;">No assets uploaded in this section.</div>
                            <?php else: ?>
                                <?php foreach ($assets as $asset): ?>
                                    <div class="asset-item" data-name="<?php echo htmlspecialchars($asset['name']); ?>" data-ext="<?php echo htmlspecialchars($asset['ext']); ?>" style="display: flex; align-items: center; justify-content: space-between; padding: 4px 6px; background-color: var(--bg-panel); border-radius: 3px; font-size: 12.5px;">
                                        <span class="asset-name" style="cursor: pointer; color: var(--accent-blue);" title="Click to insert markdown link"><?php echo htmlspecialchars($asset['name']); ?></span>
                                        <span style="font-size: 10.5px; color: var(--text-muted);"><?php echo round($asset['size'] / 1024, 1); ?> KB</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="flex: 1; display: flex; flex-direction: column;">
                    <label for="markdown_body">Markdown Body</label>
                    <textarea class="editor-textarea" id="markdown_body" name="markdown_body" placeholder="Write in Markdown..."><?php echo htmlspecialchars($markdownBody); ?></textarea>
                </div>

                <button type="submit" class="btn-save">💾 Save and Rebuild</button>
            </form>

            <!-- Right Pane Live Preview -->
            <div class="pane-preview">
                <article id="previewContainer">
                    <!-- Dynamic preview rendered here -->
                </article>
            </div>
        </div>
    </div>

    <!-- Live Preview Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const titleInput = document.getElementById('title');
            const mdTextarea = document.getElementById('markdown_body');
            const preview = document.getElementById('previewContainer');
            const aiCheckbox = document.getElementById('ai_generated');

            function updatePreview() {
                const titleText = titleInput.value.trim();
                const mdText = mdTextarea.value;
                
                // Construct temporary full markdown
                let fullMarkdown = '';
                if (titleText) {
                    fullMarkdown += '# ' + titleText + '\n\n';
                }
                fullMarkdown += mdText;

                // Compile client-side in real-time using md2web.js
                const cleanMarkdown = fullMarkdown.replace(/<!--\s*(ai-generated|contributors):\s*(.+?)\s*-->\s*/gi, '');
                preview.innerHTML = md2web.parseMarkdown(cleanMarkdown, '<?php echo htmlspecialchars($file); ?>');
                
                // Set data-generated attribute based on checkbox state
                if (aiCheckbox.checked) {
                    preview.setAttribute('data-generated', 'true');
                } else {
                    preview.removeAttribute('data-generated');
                }
            }

            // Bind listeners for real-time updates
            titleInput.addEventListener('input', updatePreview);
            mdTextarea.addEventListener('input', updatePreview);
            aiCheckbox.addEventListener('change', updatePreview);

            // Asset Uploader Logic
            const fileInput = document.getElementById('asset_file_input');
            const selectBtn = document.getElementById('btn_select_file');
            const uploadBtn = document.getElementById('btn_upload_asset');
            const uploadStatus = document.getElementById('upload_status');
            const assetsList = document.getElementById('assets_list');

            selectBtn.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    uploadStatus.textContent = `Selected: ${fileInput.files[0].name}`;
                    uploadStatus.style.color = 'var(--text-main)';
                    uploadBtn.disabled = false;
                    uploadBtn.style.cursor = 'pointer';
                    uploadBtn.style.opacity = '1';
                } else {
                    uploadStatus.textContent = 'No file selected';
                    uploadStatus.style.color = 'var(--text-muted)';
                    uploadBtn.disabled = true;
                    uploadBtn.style.cursor = 'not-allowed';
                    uploadBtn.style.opacity = '0.5';
                }
            });

            uploadBtn.addEventListener('click', () => {
                if (fileInput.files.length === 0) return;
                
                uploadStatus.textContent = 'Uploading...';
                uploadStatus.style.color = 'var(--accent-yellow)';
                uploadBtn.disabled = true;
                
                const formData = new FormData();
                formData.append('ajax_upload', '1');
                formData.append('asset_file', fileInput.files[0]);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        uploadStatus.textContent = data.message;
                        uploadStatus.style.color = 'var(--accent-green)';
                        fileInput.value = '';
                        uploadBtn.disabled = true;
                        uploadBtn.style.cursor = 'not-allowed';
                        uploadBtn.style.opacity = '0.5';
                        
                        // Rebuild asset list in UI
                        renderAssetList(data.assets);
                        
                        // Refresh preview
                        updatePreview();
                    } else {
                        uploadStatus.textContent = data.error || 'Upload failed';
                        uploadStatus.style.color = 'var(--text-bright)';
                        uploadBtn.disabled = false;
                        uploadBtn.style.cursor = 'pointer';
                        uploadBtn.style.opacity = '1';
                    }
                })
                .catch(err => {
                    uploadStatus.textContent = 'Connection error occurred.';
                    uploadStatus.style.color = 'var(--text-bright)';
                    uploadBtn.disabled = false;
                    uploadBtn.style.cursor = 'pointer';
                    uploadBtn.style.opacity = '1';
                });
            });

            function renderAssetList(assets) {
                if (!assets || assets.length === 0) {
                    assetsList.innerHTML = '<div id="no_assets_text" style="font-size: 12px; color: var(--text-muted); font-style: italic;">No assets uploaded in this section.</div>';
                    return;
                }
                
                assetsList.innerHTML = '';
                assets.forEach(asset => {
                    const div = document.createElement('div');
                    div.className = 'asset-item';
                    div.dataset.name = asset.name;
                    div.dataset.ext = asset.ext;
                    div.style.display = 'flex';
                    div.style.alignItems = 'center';
                    div.style.justifyContent = 'space-between';
                    div.style.padding = '4px 6px';
                    div.style.backgroundColor = 'var(--bg-panel)';
                    div.style.borderRadius = '3px';
                    div.style.fontSize = '12.5px';
                    
                    div.innerHTML = `
                        <span class="asset-name" style="cursor: pointer; color: var(--accent-blue);" title="Click to insert markdown link">${asset.name}</span>
                        <span style="font-size: 10.5px; color: var(--text-muted);">${(asset.size / 1024).toFixed(1)} KB</span>
                    `;
                    
                    div.querySelector('.asset-name').addEventListener('click', () => {
                        insertAssetMarkdown(asset.name, asset.ext);
                    });
                    
                    assetsList.appendChild(div);
                });
            }

            function insertAssetMarkdown(name, ext) {
                const isImage = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext.toLowerCase());
                const syntax = isImage ? `![image](${name})` : `[document](${name})`;
                
                const start = mdTextarea.selectionStart;
                const end = mdTextarea.selectionEnd;
                const text = mdTextarea.value;
                
                mdTextarea.value = text.substring(0, start) + syntax + text.substring(end);
                mdTextarea.focus();
                mdTextarea.selectionStart = mdTextarea.selectionEnd = start + syntax.length;
                
                // Trigger preview refresh
                updatePreview();
            }

            // Bind click handlers to existing static assets in the list
            document.querySelectorAll('.asset-item').forEach(item => {
                item.querySelector('.asset-name').addEventListener('click', () => {
                    insertAssetMarkdown(item.dataset.name, item.dataset.ext);
                });
            });

            // Initial render
            updatePreview();
        });
    </script>
</body>
</html>
