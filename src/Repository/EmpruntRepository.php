<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Emprunt;
use App\Model\Exception\LivreIndisponibleException;
use DateTimeImmutable;
use PDO;
use PDOException;

final class EmpruntRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function trouver(int $id): ?Emprunt
    {
        $statement = $this->pdo->prepare('SELECT * FROM emprunts WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function actifPourLivre(int $livreId): ?Emprunt
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            SELECT * FROM emprunts
            WHERE livre_id = :livre_id AND retourne_le IS NULL
            LIMIT 1
        SQL);
        $statement->execute(['livre_id' => $livreId]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    /** @return list<Emprunt> */
    public function historiquePourLivre(int $livreId): array
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            SELECT * FROM emprunts
            WHERE livre_id = :livre_id
            ORDER BY emprunte_le DESC, id DESC
        SQL);
        $statement->execute(['livre_id' => $livreId]);

        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    public function creer(Emprunt $emprunt): int
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            INSERT INTO emprunts (
                livre_id,
                emprunteur_nom,
                emprunteur_email,
                emprunte_le,
                echeance_le,
                retourne_le
            ) VALUES (
                :livre_id,
                :nom,
                :email,
                :emprunte_le,
                :echeance_le,
                NULL
            )
        SQL);

        try {
            $statement->execute([
                'livre_id' => $emprunt->livreId,
                'nom' => trim($emprunt->emprunteurNom),
                'email' => trim($emprunt->emprunteurEmail),
                'emprunte_le' => $emprunt->emprunteLe->format('Y-m-d'),
                'echeance_le' => $emprunt->echeanceLe->format('Y-m-d'),
            ]);
        } catch (PDOException $exception) {
            if (str_contains($exception->getMessage(), 'UNIQUE constraint failed')) {
                throw new LivreIndisponibleException('Ce livre est déjà emprunté.', 0, $exception);
            }
            throw $exception;
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function enregistrerRetour(int $id, DateTimeImmutable $date): bool
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            UPDATE emprunts
            SET retourne_le = :retourne_le
            WHERE id = :id AND retourne_le IS NULL
        SQL);
        $statement->execute([
            'id' => $id,
            'retourne_le' => $date->format('Y-m-d'),
        ]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string,mixed> $row */
    private function hydrate(array $row): Emprunt
    {
        return new Emprunt(
            (int) $row['id'],
            (int) $row['livre_id'],
            (string) $row['emprunteur_nom'],
            (string) $row['emprunteur_email'],
            new DateTimeImmutable((string) $row['emprunte_le']),
            new DateTimeImmutable((string) $row['echeance_le']),
            $row['retourne_le'] === null ? null : new DateTimeImmutable((string) $row['retourne_le']),
        );
    }
}
