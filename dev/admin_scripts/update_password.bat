@echo off
title Update Admin Password
echo ===================================================
echo Update Admin Password
echo ===================================================
echo.

:: Check if local portable PHP exists and runs successfully (relative to dev/admin_scripts/)
if exist "%~dp0..\..\php\php.exe" (
    "%~dp0..\..\php\php.exe" "%~dp0update_password.php"
) else (
    where php >nul 2>nul
    if %errorlevel% equ 0 (
        php "%~dp0update_password.php"
    ) else (
        echo [ERROR] PHP environment not detected. Please run install.bat first.
        echo.
        pause
        exit /b 1
    )
)

echo.
pause
