<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "NO_LOG_FILE";
    exit;
}
$lines = file($logFile);
$lastLines = array_slice($lines, -150);
echo implode("", $lastLines);
