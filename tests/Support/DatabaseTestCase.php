<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use PDO;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;
    protected LivreRepository $livres;
    protected EmpruntRepository $emprunts;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON');
        $this->pdo->exec((string) file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql'));

        $this->livres = new LivreRepository($this->pdo);
        $this->emprunts = new EmpruntRepository($this->pdo);
    }
}
