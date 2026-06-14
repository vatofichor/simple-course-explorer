<?php
// admin/auth.php
/*
  Copyright (c) 2026:
  vatofichor - Sebastian Mass     [>_<]
  & Assisted By Gemini Antigravity \|\
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rootDir = dirname(__DIR__);
$configFile = $rootDir . '/config.php';

if (!file_exists($configFile)) {
    die("Error: config.php not found. Please run install.bat or install.sh first to set your admin password.");
}

$config = require $configFile;

// Helper to check authentication status and redirect if not authorized
function require_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /admin/login.php');
        exit;
    }
}
?>
