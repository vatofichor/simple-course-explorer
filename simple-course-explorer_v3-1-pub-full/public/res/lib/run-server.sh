#!/bin/bash
# Course Explorer Linux/macOS Startup Script
# Copyright (c) 2026: vatofichor - Sebastian Mass

echo "Starting Course Explorer Local Server..."
echo.

# Check if php is installed
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP is not installed on this system."
    echo "Please install PHP using your package manager (e.g. 'sudo apt install php-cli' or 'brew install php')."
    exit 1
fi

echo "[OK] Using system PHP environment..."
# Launch browser in background depending on OS
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if command -v xdg-open &> /dev/null; then
        xdg-open http://localhost:8000 &
    fi
elif [[ "$OSTYPE" == "darwin"* ]]; then
    open http://localhost:8000 &
fi

php -d opcache.enable=0 -S localhost:8000
