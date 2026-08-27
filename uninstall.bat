@echo off
title Course Explorer Uninstaller
echo ===================================================
echo Removing unpacked startup scripts...
echo ===================================================
echo.

if exist "%~dp0run-server.bat" (
    del "%~dp0run-server.bat"
    echo Removed 'run-server.bat' from the root.
)

if exist "%~dp0run-server.sh" (
    del "%~dp0run-server.sh"
    echo Removed 'run-server.sh' from the root.
)

echo.
echo Startup scripts removed. The local 'php/' environment was kept.
echo You can delete the entire folder manually if you want to completely remove everything.
echo.
pause
