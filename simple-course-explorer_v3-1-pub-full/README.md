# Simple Course Explorer & Content Creator Studio

Welcome to **Simple Course Explorer**, a retro-premium, lightweight, offline-capable learning management system and visual content creator studio. 

Course Explorer is designed for educators, writers, and course creators who want a **100% free, private, database-free software** to author and publish beautiful online courses without writing code or dealing with complex web platforms.

---

## The Creator Studio & GUI Editor

Unlike traditional systems that require coding or manual database configuration, Course Explorer features a built-in **Instructor Control Center & Creator Studio** that works entirely from your web browser.

### 1. Split-Screen Studio Editor
- **Real-Time Preview:** Write in simple, natural Markdown on the left pane and instantly see the styled, high-contrast final layout on the right.
- **Immediate Feedback:** Real-time rendering of headers, lists, styled tables, code blocks, quote sections, custom alert boxes, and formulas.

### 2. Integrated Media & Asset Manager
- **Direct Uploads:** Drag, drop, or select files (images, audio, video, PDFs) to upload them directly into your current lesson module directory.
- **Single-Click Insert:** Click any file name in the uploaded assets list to instantly inject the correct Markdown tag (e.g. `![image](chart.png)` or `[document](manual.pdf)`) at your cursor. No typing file paths or guessing markdown syntax!
- **Auto-Syncing:** Images and documents are automatically organized and synced to the public student viewer.

### 3. Publishing & Metadata Control
- **Draft vs. Ready States:** Toggle the **Ready / Published** checkbox in the editor to control visibility. Unready lessons stay securely as drafts (completely hidden from students and skipped during compilation).
- **AI-Assisted Content Flag:** A simple checkbox lets you flag AI-assisted lessons, automatically rendering a clean contributor/assistance badge for student clarity.
- **Reviewed & Contributor Credits:** Add reviewer names to display verified credit badges on your course pages.

### 4. Instructor Control Center (Dashboard)
- **Course Tree View:** View your entire course structure, organized into sections and modules.
- **Visual Status Badges:** Instantly identify drafts, AI-generated pages, and reviewed articles at a glance.
- **One-Click Rebuild:** Click **Rebuild Course HTML** on the dashboard to compile all changes into fast, static content.

---

## Quick Start (Desktop Native Mode)

If you are a parent, teacher, or content creator running Course Explorer locally on a personal computer (no technical hosting skills required), follow these steps:

### 1. Installation
1. Locate the installation script in the root directory:
   - **Windows:** Double-click `install.bat`.
   - **Linux/macOS:** Open a terminal and run `./install.sh`.
2. Follow the prompts. The installer will prepare the application and ask you to enter an **admin password** to secure your local Creator Studio.

### 2. Start the Studio
- **Windows:** Double-click `run-server.bat` in the root folder.
- **Linux/macOS:** Run `./run-server.sh` in the terminal.
- Your default web browser will automatically open the course viewer at: `http://localhost:8000/`
- To log in to the Creator Studio, click **Admin** in the navigation menu or navigate to `http://localhost:8000/admin/`.

*To close the application, simply close the command-prompt window or terminal.*

---

## Developer & Host Deployment (Web Server Mode)

For web administrators, systems engineers, and developers deploying Course Explorer on remote host environments:

### 1. Requirements & Dependencies
- **Core Engine:** PHP (compatible with PHP 7.4 through PHP 8.x). **No database required.**
- **Client Render Engine:** JavaScript (ES3+ compatible).
- **Environment:** Apache Web Server (recommended, support file `.htaccess` included).

### 2. Production Deployment & Security
When deploying to a public production web server, ensure you block or exclude administrative tooling from public access:
- **Keep in Production:** `/public/`, `/admin/`, `config.php` (holds hashed passwords, blocked by `.htaccess`).
- **Exclude from Production:** `/dev/` (contains specs, uncompiled source files, CLI tools).
- **Security Check:** Ensure the `.htaccess` rules are actively enforced by Apache to prevent direct hits on config or source trees.

### 3. CLI Rebuilding
To rebuild files on a server after modifying markdown source articles inside `/content_source/` via command line:
1. Log in to the host machine via SSH.
2. Navigate to the project root and execute the builder script:
   ```bash
   php dev/admin_scripts/convert.php
   ```
This updates the public lesson structures and updates `/public/content/content_manifest.json`.

---

## Project Structure & Specifications

Here is how the system boundaries are structured:
- **`content_source/`**: Contains raw markdown files organized into sections (edited via the page editor).
- **`public/content/`**: Contains compiled HTML outputs and the index manifest generated by the compiler.
- **`admin/`**: Houses PHP scripts for dashboard navigation, lesson editing, and authentication logic.
- **`dev/admin_scripts/`**: Holds CLI-only configuration scripts (such as password updaters, builders, and backups).
- **`dev/specs/`**: Developer specifications documenting critical subsystems and guidelines:
  - [Setup & Installation Specification](dev/specs/setup-installation-spec.md)
  - [Security & Authentication Specification](dev/specs/auth-system-spec.md)
  - [Markdown & Glyph Syntax Reference Guide](dev/specs/markdown-spec.md)
  - [CMS & Content Authoring Specification](dev/specs/cms-authoring-spec.md)
  - [Compiler Pipeline & Build Specification](dev/specs/compiler-pipeline-spec.md)

---

#&nbsp;Copyright (c) 2026:
#&nbsp;vatofichor&nbsp;-&nbsp;Sebastian Mass&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[&gt;_&lt;]
#&nbsp;&amp;&nbsp;Assisted By Gemini Antigravity&nbsp;\|\  
