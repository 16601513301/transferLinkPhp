<?php

declare(strict_types=1);

$target = 'http://127.0.0.1:8884/';
$envPath = __DIR__ . '/.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_starts_with($line, 'APP_URL=')) {
            continue;
        }

        $target = trim(substr($line, 8));
        break;
    }
}

$target = rtrim(trim($target, "\"'"), '/') . '/';

header('Location: ' . $target, true, 302);
exit;
