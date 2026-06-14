<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 
      Copyright (c) 2026:
      vatofichor - Sebastian Mass     [>_<]
      & Assisted By Gemini Antigravity \|\
    -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Explorer</title>
    <!-- Google Fonts for retro/premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Outfit:wght@300;400;500;700&display=swap"
        rel="stylesheet">
    <style>
        /* CSS Variables for theme consistency */
        :root {
            --bg-desktop: #1e222b;
            --bg-window: #2c313c;
            --bg-panel: #21252b;
            --bg-content: #1e1e24;
            --border-light: #4c5262;
            --border-dark: #181a1f;
            --text-main: #abb2bf;
            --text-bright: #e06c75;
            /* Retro accent red */
            --text-muted: #5c6370;
            --accent-green: #98c379;
            --accent-blue: #61afef;
            --accent-yellow: #e5c07b;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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

        /* Window Workspace Area */
        .workspace {
            flex: 1;
            display: flex;
            overflow: hidden;
            background-color: var(--bg-panel);
            position: relative;
        }

        /* Sidebar - Navigation / Table of Contents */
        .sidebar {
            width: var(--sidebar-width);
            border-right: 2px solid var(--border-dark);
            background-color: var(--bg-panel);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Search Section */
        .search-container {
            padding: 10px;
            border-bottom: 1px solid var(--border-dark);
            background-color: var(--bg-window);
        }

        .search-box {
            width: 100%;
            background-color: var(--bg-panel);
            border: 1px solid var(--border-light);
            color: #fff;
            padding: 6px 10px;
            font-size: 13px;
            font-family: inherit;
            border-radius: 3px;
            outline: none;
        }

        .search-box:focus {
            border-color: var(--accent-blue);
        }

        /* TOC List */
        .toc-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px 5px;
            list-style: none;
        }

        /* Sidebar Section Headers */
        .toc-section-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--accent-blue);
            padding: 8px 10px 4px 10px;
            letter-spacing: 0.8px;
            user-select: none;
        }

        /* Sidebar List Items */
        .toc-item {
            padding: 6px 10px;
            margin: 2px 5px;
            font-size: 13px;
            cursor: pointer;
            border-radius: 3px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .toc-item:hover {
            background-color: var(--bg-window);
            color: #fff;
        }

        .toc-item.active {
            background-color: #2c384c;
            color: var(--accent-blue);
            font-weight: 500;
            border-left: 3px solid var(--accent-blue);
            padding-left: 7px;
        }

        /* Main Reader Content Panel */
        .content-panel {
            flex: 1;
            background-color: var(--bg-content);
            overflow-y: auto;
            padding: 40px;
            scroll-behavior: smooth;
        }

        /* Article Wrapper Styling */
        article {
            max-width: 850px;
            margin: 0 auto 20px auto;
            padding-bottom: 140px;
            border-bottom: 2px dashed var(--border-light);
            line-height: 1.7;
            font-size: 15.5px;
            color: var(--text-main);
        }

        /* Content Badges */
        .content-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 3px;
            margin-bottom: 15px;
            user-select: none;
            line-height: 1.4;
        }

        .badge-generated {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background-color: rgba(138, 146, 161, 0.08);
            color: #8a92a1;
            border: 1px solid rgba(138, 146, 161, 0.15);
            padding: 3px 8px;
        }

        .badge-modified {
            background-color: rgba(138, 146, 161, 0.08);
            color: #8a92a1;
            border: 1px solid rgba(138, 146, 161, 0.15);
        }

        article:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Section Header Divider banner */
        .active-section-header {
            max-width: 850px;
            margin: 20px auto 10px auto;
            background-color: var(--bg-panel);
            border: 2px solid;
            border-color: var(--border-dark) var(--border-light) var(--border-light) var(--border-dark);
            padding: 15px 25px;
            font-size: 15px;
            font-weight: 700;
            color: var(--accent-blue);
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
            user-select: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .active-section-header .folder-icon {
            font-size: 18px;
            color: var(--accent-yellow);
        }

        /* Article Headers */
        article h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-top: 40px;
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

        article h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-top: 24px;
            margin-bottom: 12px;
        }

        article h6 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 22px;
            margin-bottom: 10px;
        }

        article h1:first-child,
        article h2:first-child,
        article h3:first-child,
        article h4:first-child,
        article h5:first-child,
        article h6:first-child {
            margin-top: 10px;
        }

        article p {
            margin-bottom: 16px;
        }

        /* Tables styling */
        article table {
            border-collapse: collapse;
            width: 100%;
            margin: 25px 0;
            font-size: 14px;
        }

        article th,
        article td {
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

        /* Blockquotes */
        article blockquote {
            border-left: 4px solid var(--accent-blue);
            padding: 12px 20px;
            margin: 20px 0;
            background-color: rgba(97, 175, 239, 0.05);
            color: #d1d5db;
            font-style: italic;
            border-radius: 0 4px 4px 0;
        }

        /* Lists */
        article ul,
        article ol {
            margin-left: 25px;
            margin-bottom: 16px;
        }

        article li {
            margin-bottom: 6px;
        }

        /* Code formatting */
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

        /* Horizontal rule */
        article hr {
            border: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-light), transparent);
            margin: 40px 0;
        }

        /* Math and Greek glyph highlight */
        .math {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            color: #a8ffb2;
            background-color: rgba(168, 255, 178, 0.08);
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 16px;
        }

        /* Links */
        article a {
            color: var(--accent-blue);
            text-decoration: none;
        }

        article a:hover {
            text-decoration: underline;
        }

        /* Status Bar at the bottom */
        .status-bar {
            height: 24px;
            background-color: var(--bg-window);
            border-top: 2px solid var(--border-dark);
            padding: 0 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: var(--text-muted);
            user-select: none;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .progress-bar-container {
            width: 100px;
            height: 8px;
            background-color: var(--bg-panel);
            border: 1px solid var(--border-light);
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-left: 6px;
        }

        .progress-bar-fill {
            height: 100%;
            background-color: var(--accent-blue);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Loading indicator */
        .loading-screen {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--bg-content);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            gap: 15px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-light);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-panel);
            border-left: 1px solid var(--border-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--bg-window);
            border: 2px solid var(--bg-panel);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--border-light);
        }

        /* Next Section Card Navigation */
        .next-section-card {
            max-width: 850px;
            margin: 40px auto 100px auto;
            background-color: var(--bg-panel);
            border: 2px solid;
            border-color: var(--border-light) var(--border-dark) var(--border-dark) var(--border-light);
            padding: 20px 24px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            user-select: none;
        }

        .next-section-card:hover {
            border-color: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(97, 175, 239, 0.15);
        }

        .next-section-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .next-section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent-blue);
        }

        .next-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .next-section-dir {
            font-size: 12px;
            color: var(--text-muted);
        }

        .next-section-arrow {
            font-size: 24px;
            color: var(--accent-blue);
            transition: transform 0.2s ease;
        }

        .next-section-card:hover .next-section-arrow {
            transform: translateX(6px);
        }

        /* Window Header Bar */
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

        .menu-toggle {
            background: none;
            border: 1px solid var(--border-light);
            color: var(--text-main);
            font-size: 16px;
            cursor: pointer;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.15s ease;
        }

        .menu-toggle:hover {
            background-color: var(--bg-panel);
            color: #fff;
            border-color: var(--accent-blue);
        }

        /* Sidebar Transition Support */
        .sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sidebar Mobile Overlay */
        .sidebar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            z-index: 99;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Desktop Sidebar Collapse State */
        @media (min-width: 769px) {
            .workspace.sidebar-collapsed .sidebar {
                width: 0;
                border-right: none;
            }
        }

        /* Responsive Layout for Mobile/Tablet */
        @media (max-width: 768px) {
            body {
                padding: 0;
            }

            .window {
                border: none;
                border-radius: 0;
                box-shadow: none;
                height: 100vh;
            }

            .sidebar {
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 100;
                transform: translateX(-100%);
                border-right: 2px solid var(--border-dark);
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.5);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .content-panel {
                padding: 20px;
            }

            article {
                padding-bottom: 80px;
            }

            article h1 {
                font-size: 24px;
                margin-top: 20px;
            }

            article h2 {
                font-size: 18px;
                margin-top: 24px;
            }

            .active-section-header {
                padding: 12px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

    <div class="window">
        <!-- Window Header -->
        <div class="window-header">
            <button id="menuToggle" class="menu-toggle" aria-label="Toggle Navigation">☰</button>
            <div class="window-title">Course Explorer</div>
            <div style="flex: 1;"></div>
        </div>

        <!-- Workspace (Sidebar + Reader) -->
        <div class="workspace">
            <!-- Loading overlay -->
            <div id="loading" class="loading-screen">
                <div class="spinner"></div>
                <div style="font-size: 13px; color: var(--text-muted);">Loading course materials...</div>
            </div>

            <!-- Sidebar mobile overlay -->
            <div id="sidebarOverlay" class="sidebar-overlay"></div>

            <!-- TOC Sidebar -->
            <div class="sidebar">
                <div class="search-container">
                    <input type="text" id="searchBox" class="search-box" placeholder="Search lessons..."
                        autocomplete="off">
                </div>
                <ul id="tocList" class="toc-list">
                    <!-- TOC elements loaded dynamically -->
                </ul>
            </div>

            <!-- Reader Content Panel -->
            <div id="contentPanel" class="content-panel">
                <!-- Articles loaded dynamically -->
            </div>
        </div>

        <!-- Status Bar -->
        <div class="status-bar">
            <div id="statusLeft">Ready</div>
            <div class="status-item">
                <span>Course Progress: <span id="progressText">0/0</span></span>
                <div class="progress-bar-container">
                    <div id="progressBar" class="progress-bar-fill"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parser library -->
    <script src="res/lib/md2web.js"></script>

    <!-- Script logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tocList = document.getElementById('tocList');
            const contentPanel = document.getElementById('contentPanel');
            const searchBox = document.getElementById('searchBox');
            const loadingScreen = document.getElementById('loading');
            const progressText = document.getElementById('progressText');
            const progressBar = document.getElementById('progressBar');
            const statusLeft = document.getElementById('statusLeft');
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const workspace = document.querySelector('.workspace');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            let courseManifest = [];
            let articleElements = [];

            // Cookie state helpers (lasts 7 days by default or minutes if specified)
            function setCookie(name, value, daysOrMinutes, isMinutes = false) {
                let expires = "";
                if (daysOrMinutes) {
                    const date = new Date();
                    const ms = isMinutes ? (daysOrMinutes * 60 * 1000) : (daysOrMinutes * 24 * 60 * 60 * 1000);
                    date.setTime(date.getTime() + ms);
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + encodeURIComponent(value || "") + expires + "; path=/";
            }

            function getCookie(name) {
                const nameEQ = name + "=";
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
                }
                return null;
            }

            // Helper to get directory path (subdir) from file path
            function getSubdir(relativePath) {
                const parts = relativePath.split('/');
                parts.pop(); // remove file name
                return parts.join('/');
            }

            // Helper to clean section names by stripping leading numbers from all parts
            function cleanSectionName(section) {
                if (!section) return '';
                return section.split('/').map(part => part.trim().replace(/^\d+/, '')).join(' / ');
            }

            // 1. Fetch manifest.json to get list of files
            fetch('content/content_manifest.json')
                .then(res => res.json())
                .then(manifest => {
                    courseManifest = manifest;
                    buildTOC();

                    // Restore state using cookie
                    const savedArticle = getCookie('last_article');
                    const savedSubdir = getCookie('last_page');
                    let initialOrder = 0;

                    if (savedArticle !== null) {
                        const parsed = parseInt(savedArticle, 10);
                        if (!isNaN(parsed) && parsed >= 0 && parsed < courseManifest.length) {
                            initialOrder = parsed;
                        }
                    } else if (savedSubdir) {
                        const matchedArt = courseManifest.find(a => getSubdir(a.relative_path) === savedSubdir);
                        if (matchedArt) {
                            initialOrder = matchedArt.order;
                        }
                    }
                    loadAndGoToArticle(initialOrder);
                })
                .catch(err => {
                    console.error('Failed to load course manifest:', err);
                    loadingScreen.innerHTML = `<div style="color: #ff5f56; font-weight: bold;">Error: Failed to load manifest file!</div>`;
                });

            // 2. Build the full Sidebar Table of Contents
            function buildTOC() {
                let currentSection = '';
                courseManifest.forEach(articleInfo => {
                    if (articleInfo.section !== currentSection) {
                        currentSection = articleInfo.section;
                        const header = document.createElement('li');
                        header.className = 'toc-section-header';
                        header.textContent = cleanSectionName(currentSection);
                        tocList.appendChild(header);
                    }

                    const tocItem = document.createElement('li');
                    tocItem.className = 'toc-item';
                    tocItem.id = `toc-item-${articleInfo.order}`;
                    tocItem.dataset.order = articleInfo.order;
                    const filename = articleInfo.relative_path.split('/').pop().replace(/\.html$/i, '');
                    tocItem.innerHTML = `📄 ${filename}`;
                    tocItem.addEventListener('click', () => {
                        loadAndGoToArticle(articleInfo.order);
                    });
                    tocList.appendChild(tocItem);
                });
            }

            const loadedSubdirs = new Map(); // Keep track of loaded directory DOM elements

            // Helper to fetch and append a single article to a section container
            async function fetchAndAppendArticle(container, art, subdir) {
                try {
                    const response = await fetch('content/' + art.relative_path);
                    const rawHtml = await response.text();

                    const articleDiv = document.createElement('article');
                    articleDiv.id = `article-container-${art.order}`;
                    articleDiv.innerHTML = renderArticle(rawHtml, 'content/' + art.relative_path);

                    // Extract and prepend contribution badges
                    const tagMatch = rawHtml.match(/<article([^>]*)>/i);
                    if (tagMatch) {
                        const attrs = tagMatch[1];
                        if (/data-modified/i.test(attrs)) {
                            const matchVal = attrs.match(/data-modified=["']([^"']+)["']/i);
                            const contributors = matchVal ? matchVal[1] : '';
                            const badge = document.createElement('div');
                            badge.className = 'content-badge badge-modified';
                            badge.textContent = contributors 
                                ? `⚡ Generated and Modified Content. Contributors: ${contributors}`
                                : `⚡ Generated and Modified Content.`;
                            articleDiv.insertBefore(badge, articleDiv.firstChild);
                        } else if (/data-generated/i.test(attrs)) {
                            const badge = document.createElement('div');
                            badge.className = 'content-badge badge-generated';
                            badge.textContent = '⚡ Generated / Assisted. Contributors welcome, add your name!';
                            articleDiv.insertBefore(badge, articleDiv.firstChild);
                        }
                    }

                    container.appendChild(articleDiv);

                    // Save references for scrollspy checks if not already added
                    if (!articleElements.some(el => el.order === art.order)) {
                        articleElements.push({
                            order: art.order,
                            container: articleDiv,
                            subdir: subdir
                        });
                        // Keep references sorted to maintain correct scrollspy order
                        articleElements.sort((a, b) => a.order - b.order);
                    }
                } catch (e) {
                    console.error(`Failed to load article ${art.relative_path}:`, e);
                }
            }

            // Helper to append the Next Section navigation card to the container
            function appendNextSectionCard(container, lastArt) {
                const existingCard = container.querySelector('.next-section-card');
                if (existingCard) {
                    existingCard.remove();
                }

                const nextArt = courseManifest.find(a => a.order === lastArt.order + 1);
                if (nextArt) {
                    const nextCard = document.createElement('div');
                    nextCard.className = 'next-section-card';
                    
                    const nextCleanSection = cleanSectionName(nextArt.section);
                    const nextFilename = nextArt.relative_path.split('/').pop().replace(/\.html$/i, '').replace(/[-_]/g, ' ');
                    
                    nextCard.innerHTML = `
                        <div class="next-section-info">
                            <div class="next-section-label">Next Section</div>
                            <div class="next-section-title">${nextArt.title || nextFilename}</div>
                            <div class="next-section-dir">Folder: ${nextCleanSection}</div>
                        </div>
                        <div class="next-section-arrow">➜</div>
                    `;
                    nextCard.addEventListener('click', () => {
                        loadAndGoToArticle(nextArt.order);
                    });
                    container.appendChild(nextCard);
                }
            }

            // Load the next batch of 5 articles for the active section
            async function loadNextBatch(subdir) {
                const state = loadedSubdirs.get(subdir);
                if (!state || state.isLoading || state.loadedCount >= state.allArticles.length) return;

                state.isLoading = true;
                const nextLoadCount = Math.min(state.loadedCount + 5, state.allArticles.length);
                const batchArticles = state.allArticles.slice(state.loadedCount, nextLoadCount);

                for (let i = 0; i < batchArticles.length; i++) {
                    await fetchAndAppendArticle(state.container, batchArticles[i], subdir);
                }

                state.loadedCount = nextLoadCount;
                state.isLoading = false;

                if (state.loadedCount === state.allArticles.length) {
                    appendNextSectionCard(state.container, state.allArticles[state.allArticles.length - 1]);
                }
            }

            // 3. Load directory by directory and navigate to target article
            async function loadAndGoToArticle(order) {
                if (!courseManifest || courseManifest.length === 0) {
                    loadingScreen.style.display = 'none';
                    return;
                }
                const targetArticle = courseManifest.find(a => a.order === order);
                if (!targetArticle) {
                    loadingScreen.style.display = 'none';
                    return;
                }

                const subdir = getSubdir(targetArticle.relative_path);

                try {
                    // Hide all loaded subdir containers
                    loadedSubdirs.forEach((state) => {
                        state.container.style.display = 'none';
                    });

                    // Load the subdir if not already cached
                    if (!loadedSubdirs.has(subdir)) {
                        loadingScreen.style.display = 'flex';

                        const container = document.createElement('div');
                        container.className = 'subdir-container';

                        // Add Section Header Banner
                        const sectionHeader = document.createElement('div');
                        sectionHeader.className = 'active-section-header';
                        sectionHeader.innerHTML = `<span class="folder-icon">📁</span> Working Set: ${cleanSectionName(targetArticle.section)}`;
                        container.appendChild(sectionHeader);

                        // Find all articles in this subdir
                        const subdirArticles = courseManifest.filter(a => getSubdir(a.relative_path) === subdir);
                        const targetIdx = subdirArticles.findIndex(a => a.order === order);
                        
                        // If the section has 5 or fewer articles, load all upfront and skip lazy loading
                        const initialLoadCount = subdirArticles.length <= 5 ? subdirArticles.length : Math.max(5, targetIdx + 1);

                        // Load initial set of articles
                        for (let i = 0; i < Math.min(initialLoadCount, subdirArticles.length); i++) {
                            await fetchAndAppendArticle(container, subdirArticles[i], subdir);
                        }

                        // Save state
                        const state = {
                            container: container,
                            allArticles: subdirArticles,
                            loadedCount: Math.min(initialLoadCount, subdirArticles.length),
                            isLoading: false
                        };
                        loadedSubdirs.set(subdir, state);

                        // If fully loaded, append Next Section card
                        if (state.loadedCount === state.allArticles.length) {
                            appendNextSectionCard(container, state.allArticles[state.allArticles.length - 1]);
                        }

                        contentPanel.appendChild(container);
                    } else {
                        // If cached, make sure the target article is loaded
                        const state = loadedSubdirs.get(subdir);
                        const targetIdx = state.allArticles.findIndex(a => a.order === order);
                        if (targetIdx >= state.loadedCount) {
                            loadingScreen.style.display = 'flex';
                            state.isLoading = true;
                            
                            const batchArticles = state.allArticles.slice(state.loadedCount, targetIdx + 1);
                            for (let i = 0; i < batchArticles.length; i++) {
                                await fetchAndAppendArticle(state.container, batchArticles[i], subdir);
                            }
                            
                            state.loadedCount = targetIdx + 1;
                            state.isLoading = false;

                            if (state.loadedCount === state.allArticles.length) {
                                appendNextSectionCard(state.container, state.allArticles[state.allArticles.length - 1]);
                            }
                        }
                    }

                    // Show active directory container
                    const activeContainer = loadedSubdirs.get(subdir);
                    if (activeContainer && activeContainer.container) {
                        activeContainer.container.style.display = 'block';
                    }

                    // Save directory state in cookie
                    setCookie('last_page', subdir, 7);

                    // Scroll to target article and setup scrollspy scroll listener
                    scrollToArticle(order);
                    updateTOCAndProgress(order);

                    // On mobile, close sidebar after selecting an article
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('open');
                        sidebarOverlay.classList.remove('active');
                    }

                    // Initialize scrollspy tracking
                    contentPanel.addEventListener('scroll', handleScrollSpy);
                } catch (err) {
                    console.error('Error loading article set:', err);
                } finally {
                    loadingScreen.style.display = 'none';
                }
            }

            // Scroll to the selected article container
            function scrollToArticle(order) {
                const target = document.getElementById(`article-container-${order}`);
                if (target) {
                    contentPanel.scrollTop = target.offsetTop - contentPanel.offsetTop;
                }
            }

            // Highlight TOC item and update progress footer
            function updateTOCAndProgress(order) {
                const tocItems = document.querySelectorAll('.toc-item');
                tocItems.forEach(item => {
                    if (parseInt(item.dataset.order) === order) {
                        item.classList.add('active');
                        const itemTop = item.offsetTop;
                        const sidebarHeight = tocList.clientHeight;
                        if (itemTop < tocList.scrollTop || itemTop + item.clientHeight > tocList.scrollTop + sidebarHeight) {
                            tocList.scrollTop = itemTop - sidebarHeight / 2;
                        }
                    } else {
                        item.classList.remove('active');
                    }
                });

                const progressVal = order + 1;
                progressText.textContent = `${progressVal}/${courseManifest.length}`;
                progressBar.style.width = `${(progressVal / courseManifest.length) * 100}%`;

                if (courseManifest[order]) {
                    const filename = courseManifest[order].relative_path.split('/').pop().replace(/\.html$/i, '');
                    statusLeft.textContent = `Reading: ${filename} (${cleanSectionName(getSubdir(courseManifest[order].relative_path))})`;
                    // Persist the last scrolled-into article order in a cookie for 30 minutes
                    setCookie('last_article', order, 30, true);
                }
            }

            // Active element highlighting on scroll within the active directory
            function handleScrollSpy() {
                const panelTop = contentPanel.scrollTop;
                const panelHeight = contentPanel.clientHeight;
                const scrollHeight = contentPanel.scrollHeight;

                const visibleSubdir = getCookie('last_page') || getSubdir(courseManifest[0].relative_path);
                
                // Progressive lazy loading: load next batch of articles if scrolling near bottom
                const state = loadedSubdirs.get(visibleSubdir);
                if (state && state.loadedCount < state.allArticles.length && !state.isLoading) {
                    if (panelTop + panelHeight >= scrollHeight - 350) {
                        loadNextBatch(visibleSubdir);
                    }
                }

                const currentSubdirArticles = articleElements.filter(el => el.subdir === visibleSubdir);

                if (currentSubdirArticles.length === 0) return;

                let activeOrder = currentSubdirArticles[0].order;

                for (let i = 0; i < currentSubdirArticles.length; i++) {
                    const el = currentSubdirArticles[i];
                    const offsetTop = el.container.offsetTop - contentPanel.offsetTop;
                    const offsetBottom = offsetTop + el.container.clientHeight;

                    if (panelTop + (panelHeight / 3) >= offsetTop && panelTop + (panelHeight / 3) < offsetBottom) {
                        activeOrder = el.order;
                        break;
                    }
                }

                if (panelTop + panelHeight >= scrollHeight - 5) {
                    activeOrder = currentSubdirArticles[currentSubdirArticles.length - 1].order;
                }

                updateTOCAndProgress(activeOrder);
            }

            // Search filtering logic (filters the sidebar TOC items)
            searchBox.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();

                courseManifest.forEach(art => {
                    const tocItem = document.getElementById(`toc-item-${art.order}`);
                    if (!tocItem) return;

                    const filename = art.relative_path.split('/').pop().replace(/\.html$/i, '');
                    const isMatch = filename.toLowerCase().includes(query) || cleanSectionName(art.section).toLowerCase().includes(query);
                    tocItem.style.display = isMatch || query === '' ? '' : 'none';
                });

                // Hide headers with no visible items
                const sectionHeaders = document.querySelectorAll('.toc-section-header');
                sectionHeaders.forEach(header => {
                    let next = header.nextElementSibling;
                    let hasVisibleItems = false;
                    while (next && !next.classList.contains('toc-section-header')) {
                        if (next.style.display !== 'none') {
                            hasVisibleItems = true;
                            break;
                        }
                        next = next.nextElementSibling;
                    }
                    header.style.display = hasVisibleItems ? '' : 'none';
                });
            });



            // Article renderer logic
            function renderArticle(rawContent, relativePath) {
                // Check if the wrapper tag specifies data-markdown
                const hasMarkdown = /<article[^>]*data-markdown/i.test(rawContent);
                
                // Strip the surrounding article wrapper
                const stripped = rawContent.replace(/<article[^>]*>([\s\S]*?)<\/article>/i, '$1').trim();

                if (hasMarkdown) {
                    // Fallback: Parse markdown dynamically in browser
                    return parseMarkdown(stripped, relativePath);
                }

                // Primary (Option B): Inject pre-rendered static HTML directly
                return stripped;
            }



            // Toggle sidebar visibility
            function toggleSidebar() {
                const isMobile = window.innerWidth <= 768;
                if (isMobile) {
                    sidebar.classList.toggle('open');
                    if (sidebar.classList.contains('open')) {
                        sidebarOverlay.classList.add('active');
                    } else {
                        sidebarOverlay.classList.remove('active');
                    }
                } else {
                    workspace.classList.toggle('sidebar-collapsed');
                }
            }

            menuToggle.addEventListener('click', toggleSidebar);

            sidebarOverlay.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
            });
        });
    </script>
</body>

</html>