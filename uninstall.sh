#!/bin/bash
# Course Explorer Uninstaller for macOS/Linux
# Copyright (c) 2026: vatofichor - Sebastian Mass

echo "Removing unpacked startup scripts..."
echo.

if [ -f "./run-server.sh" ]; then
    rm "./run-server.sh"
    echo "Removed 'run-server.sh' from the root."
fi

if [ -f "./run-server.bat" ]; then
    rm "./run-server.bat"
    echo "Removed 'run-server.bat' from the root."
fi

echo.
echo "Startup scripts removed. The local 'php/' environment (if downloaded) was kept."
echo "You can delete the entire folder manually if you want to completely remove everything."
