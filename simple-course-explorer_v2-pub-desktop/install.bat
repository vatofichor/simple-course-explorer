@echo off
title PHP Setup and Install
echo ===================================================
echo Koine Greek Course Setup
echo ===================================================
echo.

:: 1. Check if local portable PHP exists and runs successfully
if exist "%~dp0php\php.exe" (
    "%~dp0php\php.exe" -v >nul 2>nul
    if %errorlevel% equ 0 (
        echo [OK] Local portable PHP environment is already configured and functional.
        goto unpack
    ) else (
        echo [WARNING] Local 'php/php.exe' exists but is not functional/valid.
    )
)

:: 2. Check if system PHP already exists in the PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    echo [OK] System PHP detected in your path.
    goto unpack
)

:: 3. No PHP found - download portable PHP
echo No PHP environment detected on your system.
echo Downloading portable PHP environment (approx. 32MB)...
echo This requires an active internet connection.
echo ===================================================
echo.

:: Use powershell to download and extract
powershell -NoProfile -ExecutionPolicy Bypass -Command "[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]::Tls12; echo 'Downloading PHP zip...'; Invoke-WebRequest -Uri 'https://downloads.php.net/~windows/releases/php-8.2.31-nts-Win32-vs16-x64.zip' -OutFile 'php.zip'; echo 'Extracting files...'; Expand-Archive -Path 'php.zip' -DestinationPath 'php' -Force; echo 'Cleaning up...'; Remove-Item -Path 'php.zip'; echo 'Done!'"

if not exist "%~dp0php\php.exe" (
    echo.
    echo [ERROR] Setup failed. Local 'php/php.exe' could not be configured.
    echo Please make sure you are connected to the internet and try again.
    echo.
    pause
    exit /b 1
)

echo.
echo [SUCCESS] Local PHP environment configured inside the 'php/' folder.

:unpack
echo.
echo Unpacking local server startup scripts...

:: Unpack startup scripts from lib to root
if exist "%~dp0public\res\lib\run-server.bat" (
    copy "%~dp0public\res\lib\run-server.bat" "%~dp0" >nul
    echo   - Unpacked 'run-server.bat' to the root folder.
)
if exist "%~dp0public\res\lib\run-server.sh" (
    copy "%~dp0public\res\lib\run-server.sh" "%~dp0" >nul
    echo   - Unpacked 'run-server.sh' to the root folder.
)

:: Run admin password setup
echo.
echo ===================================================
echo Configure Admin Password
echo ===================================================
if exist "%~dp0php\php.exe" (
    "%~dp0php\php.exe" "%~dp0dev\admin_scripts\update_password.php"
) else (
    php "%~dp0dev\admin_scripts\update_password.php"
)

echo.
echo Setup completed! You can now run the server by double-clicking 'run-server.bat'.
echo.
pause
