<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Point unique de création de la connexion PDO.
 *
 * Le reste de l'application dépend de PDO, et non du chemin du fichier ou
 * des options techniques nécessaires à SQLite.
 */
final class ConnectionFactory
{
    public static function sqlite(string $databasePath): PDO
    {
        $directory = dirname($databasePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException("Impossible de créer le dossier {$directory}.");
        }

        $pdo = new PDO('sqlite:' . $databasePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }
}
