<?php
// admin/dashboard.php
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
$manifestFile = $publicContentDir . '/content_manifest.json';

// Handle course rebuild action
if (isset($_GET['action']) && $_GET['action'] === 'rebuild') {
    // Run the conversion script
    $convertScript = $rootDir . '/dev/admin_scripts/convert.php';
    if (file_exists($convertScript)) {
        // Run via CLI to preserve identical compilation flow
        $cmd = 'php ' . escapeshellarg($convertScript);
        shell_exec($cmd);
    }
    header('Location: /admin/dashboard.php?rebuilt=1');
    exit;
}

// Scan content_source directory to discover sections and lessons directly from the source tree
$sections = [];
$totalSections = 0;
$totalLessons = 0;
$publishedLessons = 0;
$draftLessons = 0;
$aiGenerated = 0;
$humanModified = 0;

if (is_dir($sourceDir)) {
    $items = scandir($sourceDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $sectionPath = $sourceDir . '/' . $item;
        if (is_dir($sectionPath)) {
            $totalSections++;
            $sections[$item] = [];
            
            // Scan for markdown files inside this section folder
            $files = scandir($sectionPath);
            foreach ($files as $fileItem) {
                $filePath = $sectionPath . '/' . $fileItem;
                if (is_file($filePath) && strtolower(pathinfo($fileItem, PATHINFO_EXTENSION)) === 'md') {
                    $totalLessons++;
                    
                    // Read file
                    $mdContent = file_get_contents($filePath);
                    
                    // Extract metadata
                    $isReady = false;
                    if (preg_match('/<!--\s*ready:\s*(false|true)\s*-->/i', $mdContent, $readyMatches)) {
                        $isReady = (strtolower($readyMatches[1]) === 'true');
                    }
                    
                    $isAiGenerated = true;
                    if (preg_match('/<!--\s*ai-generated:\s*(false|true)\s*-->/i', $mdContent, $aiMatches)) {
                        $isAiGenerated = (strtolower($aiMatches[1]) === 'true');
                    }
                    
                    $contributors = '';
                    if (preg_match('/<!--\s*contributors:\s*(.+?)\s*-->/i', $mdContent, $contribMatches)) {
                        $contributors = trim($contribMatches[1]);
                    }
                    
                    // Extract title from H1
                    $title = '';
                    if (preg_match('/^\s*#\s+(.+)$/m', $mdContent, $matches)) {
                        $title = trim($matches[1]);
                    }
                    if (empty($title)) {
                        $title = pathinfo($fileItem, PATHINFO_FILENAME);
                        $title = preg_replace('/^\d+[-_]/', '', $title);
                        $title = str_replace(array('-', '_'), ' ', $title);
                    }
                    
                    // Stats
                    if ($isReady) {
                        $publishedLessons++;
                    } else {
                        $draftLessons++;
                    }
                    
                    if ($isAiGenerated) {
                        $aiGenerated++;
                        $type = 'generated';
                    } else {
                        $humanModified++;
                        $type = 'modified';
                    }
                    
                    // Construct HTML relative path for editor compatibility
                    $htmlRelPath = $item . '/' . preg_replace('/\.md$/i', '.html', $fileItem);
                    
                    $sections[$item][] = [
                        'relative_path' => $htmlRelPath,
                        'title' => $title,
                        'ready' => $isReady,
                        'type' => $type,
                        'contributors' => $contributors
                    ];
                }
            }
        }
    }
    // Sort sections alphabetically/numerically
    ksort($sections);
}

// Check for success alerts
$rebuiltSuccess = isset($_GET['rebuilt']) && $_GET['rebuilt'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - Course Explorer</title>
    <!-- Google Fonts for retro/premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
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
            --sidebar-width: 250px;
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

        /* Classic Retro OS Window Frame */
        .window {
            width: 100%;
            height: 100%;
            max-width: 1032px;
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

        .workspace {
            flex: 1;
            display: flex;
            overflow: hidden;
            background-color: var(--bg-panel);
        }

        /* Sidebar Control Center */
        .sidebar {
            width: var(--sidebar-width);
            border-right: 2px solid var(--border-dark);
            background-color: var(--bg-panel);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .sidebar-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.8px;
            margin-bottom: 5px;
        }

        .btn-action {
            display: block;
            background-color: var(--bg-window);
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 12px;
            font-size: 13px;
            font-weight: 500;
            text-align: left;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-action:hover {
            border-color: var(--accent-blue);
            background-color: rgba(97, 175, 239, 0.05);
            transform: translateX(3px);
        }

        .btn-action-primary {
            background-color: var(--accent-blue);
            color: #1e222b;
            border: none;
            font-weight: 700;
            text-align: center;
        }

        .btn-action-primary:hover {
            opacity: 0.9;
            transform: none;
            background-color: var(--accent-blue);
        }

        /* Main Workspace Panel */
        .main-panel {
            flex: 1;
            background-color: var(--bg-content);
            overflow-y: auto;
            padding: 24px;
        }

        /* Stats Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 24px;
        }

        .stat-card {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            padding: 15px;
            text-align: center;
        }

        .stat-val {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
        }

        /* Tree List Hierarchy */
        .tree-container {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-dark);
            border-radius: 4px;
            padding: 10px;
        }

        .tree-section {
            margin-bottom: 15px;
        }

        .tree-section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            font-size: 14px;
            font-weight: 700;
            color: var(--accent-yellow);
            padding: 8px 10px;
            background-color: rgba(229, 192, 123, 0.05);
            border-radius: 3px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-lessons {
            margin-left: 20px;
            list-style: none;
            border-left: 1px dashed var(--border-light);
            padding-left: 10px;
        }

        .lesson-node {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            margin: 4px 0;
            border-radius: 3px;
            background-color: rgba(255, 255, 255, 0.01);
            transition: background-color 0.15s ease;
        }

        .lesson-node:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .lesson-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            color: var(--text-main);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .lesson-badge {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 6px;
            border-radius: 2px;
            font-weight: 700;
        }

        .badge-generated {
            background-color: rgba(224, 108, 117, 0.1);
            color: var(--text-bright);
            border: 1px solid rgba(224, 108, 117, 0.2);
        }

        .badge-modified {
            background-color: rgba(152, 195, 121, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(152, 195, 121, 0.2);
        }

        .badge-published {
            background-color: rgba(152, 195, 121, 0.15);
            color: var(--accent-green);
            border: 1px solid rgba(152, 195, 121, 0.3);
            margin-right: 5px;
        }

        .badge-draft {
            background-color: rgba(229, 192, 123, 0.15);
            color: var(--accent-yellow);
            border: 1px solid rgba(229, 192, 123, 0.3);
            margin-right: 5px;
        }

        .lesson-actions {
            display: flex;
            gap: 8px;
        }

        .btn-tool {
            background-color: var(--bg-window);
            border: 1px solid var(--border-light);
            color: var(--text-main);
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-tool:hover {
            border-color: var(--accent-blue);
            color: #fff;
        }

        .alert-success {
            background-color: rgba(152, 195, 121, 0.1);
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13.5px;
        }
    </style>
</head>
<body>
    <div class="window">
        <!-- Window Header -->
        <div class="window-header">
            <div class="window-title">🛡️ Instructor Control Center</div>
            <div class="header-spacer"></div>
            <a href="/public/admin/markdown-guide.html" target="_blank" class="btn-header">📖 Markdown Guide</a>
            <a href="/public/" target="_blank" class="btn-header">👁️ View Explorer</a>
            <a href="/admin/logout.php" class="btn-header" style="border-color: var(--text-bright); color: var(--text-bright);">Logout</a>
        </div>

        <!-- Workspace -->
        <div class="workspace">
            <!-- Left Action Sidebar -->
            <div class="sidebar">
                <div class="sidebar-title">Authoring Tools</div>
                <a href="/admin/new_section.php" class="btn-action">📁 Create Section</a>
                <a href="/admin/new_page.php" class="btn-action">📄 Create Lesson Page</a>
                
                <div class="sidebar-title" style="margin-top: 15px;">Compiler Actions</div>
                <a href="/admin/dashboard.php?action=rebuild" class="btn-action btn-action-primary">🔄 Rebuild Course HTML</a>
            </div>

            <!-- Main Panel -->
            <div class="main-panel">
                <?php if ($rebuiltSuccess): ?>
                    <div class="alert-success">
                        ✓ Success: The Course Explorer content files and order manifest have been successfully compiled from raw Markdown sources.
                    </div>
                <?php endif; ?>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-val"><?php echo $totalSections; ?></div>
                        <div class="stat-label">Total Sections</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-val"><?php echo $totalLessons; ?></div>
                        <div class="stat-label">Total Lessons</div>
                    </div>
                    <div class="stat-card" style="border-color: rgba(152, 195, 121, 0.4);">
                        <div class="stat-val" style="color: var(--accent-green);"><?php echo $publishedLessons; ?></div>
                        <div class="stat-label">Published</div>
                    </div>
                    <div class="stat-card" style="border-color: rgba(229, 192, 123, 0.4);">
                        <div class="stat-val" style="color: var(--accent-yellow);"><?php echo $draftLessons; ?></div>
                        <div class="stat-label">Drafts</div>
                    </div>
                </div>

                <!-- Tree Hierarchy List -->
                <div class="tree-container">
                    <?php if (empty($sections)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--text-muted); font-size: 13.5px;">
                            No course content sections found. Create a section to get started!
                        </div>
                    <?php else: ?>
                        <?php foreach ($sections as $sectionName => $sectionLessons): ?>
                            <div class="tree-section">
                                <div class="section-header">
                                    <span>📁</span>
                                    <span>Section: <?php echo htmlspecialchars(preg_replace('/^\d+[-_]/', '', $sectionName)); ?></span>
                                    <span style="font-size: 11px; color: var(--text-muted); font-weight: normal;">(Folder: <?php echo htmlspecialchars($sectionName); ?>)</span>
                                </div>
                                <ul class="section-lessons">
                                    <?php if (empty($sectionLessons)): ?>
                                        <li style="padding: 6px 10px; font-size: 12.5px; color: var(--text-muted); font-style: italic; list-style-type: none;">
                                            No lesson pages in this section yet.
                                        </li>
                                    <?php else: ?>
                                        <?php foreach ($sectionLessons as $lesson): ?>
                                            <li class="lesson-node">
                                                <div class="lesson-info">
                                                    <span>📄</span>
                                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($lesson['title']); ?></span>
                                                    <span style="font-size: 11px; color: var(--text-muted);"> (<?php echo htmlspecialchars(basename($lesson['relative_path'])); ?>)</span>
                                                    
                                                    <?php if ($lesson['ready']): ?>
                                                        <span class="lesson-badge badge-published">Published</span>
                                                    <?php else: ?>
                                                        <span class="lesson-badge badge-draft">Draft</span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($lesson['type'] === 'modified'): ?>
                                                        <span class="lesson-badge badge-modified" title="Contributors: <?php echo htmlspecialchars($lesson['contributors']); ?>">Reviewed</span>
                                                    <?php else: ?>
                                                        <span class="lesson-badge badge-generated">AI-Assisted</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="lesson-actions">
                                                    <a href="/admin/edit_page.php?file=<?php echo urlencode($lesson['relative_path']); ?>" class="btn-tool">📝 Edit</a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
