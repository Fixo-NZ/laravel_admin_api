<?php
$path = $argv[1] ?? 'database/seeders/DatabaseSeeder.php';
$lines = file($path);
foreach ($lines as $i => $line) {
    printf("%4d: %s", $i+1, rtrim($line, "\r\n"));
    echo PHP_EOL;
}
