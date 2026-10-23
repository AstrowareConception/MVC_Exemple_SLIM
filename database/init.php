<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

$settings = require dirname(__DIR__) . '/config/settings.php';
$databasePath = $settings['database_path'];
$fresh = in_array('--fresh', $argv, true);

if ($fresh && is_file($databasePath)) {
    unlink($databasePath);
}

$pdo = ConnectionFactory::sqlite($databasePath);
$pdo->exec((string) file_get_contents(__DIR__ . '/schema.sql'));

$total = (int) $pdo->query('SELECT COUNT(*) FROM livres')->fetchColumn();
if ($total === 0) {
    $pdo->exec((string) file_get_contents(__DIR__ . '/fixtures.sql'));
}

echo "Base SQLite prête : {$databasePath}\n";
echo "Livres présents : " . $pdo->query('SELECT COUNT(*) FROM livres')->fetchColumn() . "\n";
