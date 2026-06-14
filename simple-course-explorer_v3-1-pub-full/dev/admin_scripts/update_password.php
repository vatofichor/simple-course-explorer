<?php
// dev/admin_scripts/update_password.php

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "Course Explorer - Admin Password Setup...\n\n";

function promptPassword($prompt) {
    echo $prompt;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Under Windows, try PowerShell for secure input first, but only if STDIN is a TTY
        if (function_exists('stream_isatty') && stream_isatty(STDIN)) {
            $psCommand = 'powershell -NoProfile -Command "$p = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
            $pwd = shell_exec($psCommand);
            if ($pwd !== null && trim($pwd) !== '') {
                echo "\n";
                return trim($pwd);
            }
        }
        // Fallback to normal input
        return trim(fgets(STDIN));
    } else {
        // Unix stty method
        system('stty -echo');
        $pwd = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $pwd;
    }
}

$password = promptPassword("Enter new admin password: ");

if (empty($password)) {
    echo "Error: Password cannot be empty.\n";
    exit(1);
}

$confirm = promptPassword("Confirm new admin password: ");

if ($password !== $confirm) {
    echo "Error: Passwords do not match. Please try again.\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$configFile = dirname(dirname(__DIR__)) . '/config.php';

$configContent = "<?php\n" .
                 "// Admin configuration\n" .
                 "return [\n" .
                 "    'admin_password' => '" . addslashes($hash) . "',\n" .
                 "];\n";

if (file_put_contents($configFile, $configContent) !== false) {
    echo "Success: Admin password successfully updated and written to config.php.\n";
} else {
    echo "Error: Failed to write to config.php. Check write permissions.\n";
    exit(1);
}
?>
