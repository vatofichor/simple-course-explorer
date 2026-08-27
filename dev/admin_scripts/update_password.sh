#!/bin/bash
# Copyright (c) 2026:
# vatofichor - Sebastian Mass     [>_<]
# & Assisted By Gemini Antigravity \|\

echo "==================================================="
echo "Update Admin Password"
echo "==================================================="
echo

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if command -v php &> /dev/null; then
    php "$SCRIPT_DIR/update_password.php"
else
    echo "[ERROR] PHP environment not detected. Please run install.sh first."
    exit 1
fi
