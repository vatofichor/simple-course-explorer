<?php
// dev/admin_scripts/convert.php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Course Explorer - Rebuilding Content...\n\n";

$rootDir = dirname(dirname(__DIR__));
$sourceDir = $rootDir . '/content_source';
$publicContentDir = $rootDir . '/public/content';
$jsCompiler = $rootDir . '/lib/md2web-plugin/md2web.js';

// Synchronize the public student-side fallback JS engine with any compiler updates
$publicJsDest = $rootDir . '/public/res/lib/md2web.js';
if (file_exists($jsCompiler)) {
    if (!is_dir(dirname($publicJsDest))) {
        mkdir(dirname($publicJsDest), 0755, true);
    }
    copy($jsCompiler, $publicJsDest);
}

if (!is_dir($sourceDir)) {
    die("Error: content_source/ directory not found at: $sourceDir\n");
}

if (!is_dir($publicContentDir)) {
    mkdir($publicContentDir, 0755, true);
}

// Find files recursively and synchronize non-markdown media assets
function getMarkdownFiles($dir, $sourceDir, $publicContentDir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, getMarkdownFiles($path, $sourceDir, $publicContentDir));
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext === 'md') {
                $files[] = $path;
            } elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'pdf', 'mp3', 'mp4'])) {
                // Determine destination path in public/content
                $relPath = ltrim(str_replace($sourceDir, '', $path), '/\\');
                $destPath = $publicContentDir . '/' . str_replace('\\', '/', $relPath);
                
                // Ensure parent directory exists in public
                if (!is_dir(dirname($destPath))) {
                    mkdir(dirname($destPath), 0755, true);
                }
                
                // Sync the file if it is new or newer than the public version
                if (!file_exists($destPath) || filemtime($path) > filemtime($destPath)) {
                    copy($path, $destPath);
                }
            }
        }
    }
    return $files;
}

$mdFiles = getMarkdownFiles($sourceDir, $sourceDir, $publicContentDir);
sort($mdFiles); // Sort to ensure alphabetical/numeric order

$manifest = [];
$order = 0;

foreach ($mdFiles as $mdFile) {
    // Relative path from content_source
    $relPath = ltrim(str_replace($sourceDir, '', $mdFile), '/\\');
    $relPath = str_replace('\\', '/', $relPath); // Use forward slashes
    
    $pathParts = explode('/', $relPath);
    $section = (count($pathParts) > 1) ? $pathParts[0] : 'General';
    
    // Output HTML path
    $htmlRelPath = preg_replace('/\.md$/i', '.html', $relPath);
    $htmlOutputFile = $publicContentDir . '/' . $htmlRelPath;
    
    // Ensure parent directory exists
    $parentDir = dirname($htmlOutputFile);
    if (!is_dir($parentDir)) {
        mkdir($parentDir, 0755, true);
    }
    
    // Read the MD file to extract title and metadata
    $mdContent = file_get_contents($mdFile);
    
    // Check if AI generated (defaults to true if tag is missing or true)
    $isAiGenerated = true;
    if (preg_match('/<!--\s*ai-generated:\s*(false|true)\s*-->/i', $mdContent, $aiMatches)) {
        $isAiGenerated = (strtolower($aiMatches[1]) === 'true');
    }
    
    // Check if lesson is ready / published (defaults to false if missing)
    $isReady = false;
    if (preg_match('/<!--\s*ready:\s*(false|true)\s*-->/i', $mdContent, $readyMatches)) {
        $isReady = (strtolower($readyMatches[1]) === 'true');
    }
    
    // If not ready/published, clean up any public html and skip manifesting
    if (!$isReady) {
        if (file_exists($htmlOutputFile)) {
            @unlink($htmlOutputFile);
        }
        echo "Draft skipped: $relPath (deleted from public if existed)\n";
        continue;
    }
    
    // Check contributors
    $contributorsStr = '';
    if (preg_match('/<!--\s*contributors:\s*(.+?)\s*-->/i', $mdContent, $attrMatches)) {
        $contributorsStr = htmlspecialchars(trim($attrMatches[1]));
    }
    
    // Build <article> attributes dynamically
    $articleAttrs = '';
    if ($isAiGenerated) {
        $articleAttrs .= ' data-generated="true"';
    }
    if (!empty($contributorsStr)) {
        $articleAttrs .= ' data-modified="' . $contributorsStr . '"';
    }
    
    // Extract title from first # Heading
    $title = '';
    if (preg_match('/^\s*#\s+(.+)$/m', $mdContent, $matches)) {
        $title = trim($matches[1]);
    }
    if (empty($title)) {
        $title = pathinfo($mdFile, PATHINFO_FILENAME);
        $title = preg_replace('/^\d+[-_]/', '', $title); // strip ordering prefixes
        $title = str_replace(array('-', '_'), ' ', $title);
    }
    
    // Prepare temporary Markdown file with metadata comments stripped
    $tempMdFile = dirname($mdFile) . '/temp_' . basename($mdFile);
    $mdContentClean = preg_replace('/<!--\s*(ready|contributors|ai-generated):\s*(.+?)\s*-->\s*/i', '', $mdContent);
    file_put_contents($tempMdFile, $mdContentClean);

    // Compile using JS compiler if possible
    $compiled = false;
    
    // Detect environment & run JS compiler
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows: try cscript first, then Node.js
        $cmd = 'cscript /Nologo ' . escapeshellarg($jsCompiler) . ' ' . escapeshellarg($tempMdFile) . ' ' . escapeshellarg($htmlOutputFile) . ' ' . escapeshellarg($htmlRelPath);
        $output = [];
        $returnVal = 0;
        exec($cmd, $output, $returnVal);
        if ($returnVal === 0) {
            $compiled = true;
        } else {
            // Try node
            $cmd = 'node ' . escapeshellarg($jsCompiler) . ' ' . escapeshellarg($tempMdFile) . ' ' . escapeshellarg($htmlOutputFile) . ' ' . escapeshellarg($htmlRelPath);
            exec($cmd, $output, $returnVal);
            if ($returnVal === 0) {
                $compiled = true;
            }
        }
    } else {
        // Unix/macOS: try node
        $cmd = 'node ' . escapeshellarg($jsCompiler) . ' ' . escapeshellarg($tempMdFile) . ' ' . escapeshellarg($htmlOutputFile) . ' ' . escapeshellarg($htmlRelPath) . ' 2>&1';
        $output = [];
        $returnVal = 0;
        exec($cmd, $output, $returnVal);
        if ($returnVal === 0) {
            $compiled = true;
        }
    }
    
    // Clean up temporary file
    if (file_exists($tempMdFile)) {
        @unlink($tempMdFile);
    }
    
    // Fallcheck: if CLI JS compiled the output HTML file
    if (!$compiled) {
        // Fallback: write raw Markdown to HTML file, wrapped in <article data-markdown="true">
        $attrsHtml = ' data-markdown="true"' . $articleAttrs;
        $htmlContentClean = preg_replace('/<!--\s*(ready|contributors|ai-generated):\s*(.+?)\s*-->\s*/i', '', $mdContent);
        $htmlContentClean = '<article' . $attrsHtml . ">\n" . $htmlContentClean . "\n</article>";
        file_put_contents($htmlOutputFile, $htmlContentClean);
        $compiled = true;
    } else {
        // Wrap in standard <article> tags
        $htmlContent = file_get_contents($htmlOutputFile);
        if (strpos($htmlContent, '<article') === false) {
            $htmlContent = '<article' . $articleAttrs . ">\n" . $htmlContent . "\n</article>";
            file_put_contents($htmlOutputFile, $htmlContent);
        }
    }
    
    $manifest[] = [
        'relative_path' => $htmlRelPath,
        'title' => $title,
        'order' => $order++,
        'section' => $section
    ];
    
    echo "Compiled: $relPath -> $htmlRelPath\n";
}

// Write manifest
file_put_contents($publicContentDir . '/content_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nManifest updated.\n";

// The Markdown Guide is served as a static HTML file at /public/admin/markdown-guide.html and does not need to be compiled dynamically.
echo "\nRebuild complete!\n";
?>
