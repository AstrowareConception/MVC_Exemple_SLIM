<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Exception\LivreAvecHistoriqueException;
use App\Model\Exception\IsbnDejaUtiliseException;
use App\Model\Livre;
use PDO;
use PDOException;

/**
 * Seule cette classe connaît la table livres et son SQL.
 *
 * Les contrôleurs reçoivent des objets Livre : ils ne manipulent ni lignes
 * PDO brutes, ni noms de colonnes, ni détails propres à SQLite.
 */
final class LivreRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<Livre> */
    public function tous(?string $recherche = null): array
    {
        $sql = <<<'SQL'
            SELECT
                l.id,
                l.titre,
                l.auteur,
                l.isbn,
                l.annee_publication,
                CASE WHEN EXISTS (
                    SELECT 1 FROM emprunts e
                    WHERE e.livre_id = l.id AND e.retourne_le IS NULL
                ) THEN 0 ELSE 1 END AS disponible
            FROM livres l
        SQL;

        $params = [];
        $recherche = trim((string) $recherche);
        if ($recherche !== '') {
            $sql .= " WHERE (l.titre LIKE :recherche_titre ESCAPE '\\' COLLATE NOCASE"
                . " OR l.auteur LIKE :recherche_auteur ESCAPE '\\' COLLATE NOCASE)";
            $pattern = '%' . $this->echapperLike($recherche) . '%';
            $params['recherche_titre'] = $pattern;
            $params['recherche_auteur'] = $pattern;
        }
        $sql .= ' ORDER BY l.titre ASC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll();

        return array_map($this->hydrate(...), $rows);
    }

    public function trouver(int $id): ?Livre
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            SELECT
                l.id,
                l.titre,
                l.auteur,
                l.isbn,
                l.annee_publication,
                CASE WHEN EXISTS (
                    SELECT 1 FROM emprunts e
                    WHERE e.livre_id = l.id AND e.retourne_le IS NULL
                ) THEN 0 ELSE 1 END AS disponible
            FROM livres l
            WHERE l.id = :id
        SQL);
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function creer(Livre $livre): int
    {
        $statement = $this->pdo->prepare(<<<'SQL'
            INSERT INTO livres (titre, auteur, isbn, annee_publication)
            VALUES (:titre, :auteur, :isbn, :annee)
        SQL);
        try {
            $statement->execute([
                'titre' => $livre->titre,
                'auteur' => $livre->auteur,
                'isbn' => $livre->isbn,
                'annee' => $livre->anneePublication,
            ]);
        } catch (PDOException $exception) {
            $this->convertirContrainteIsbn($exception);
        }

        return (int) $this->pdo->lastInsertId();
    }

    public function modifier(Livre $livre): bool
    {
        if ($livre->id === null) {
            return false;
        }

        $statement = $this->pdo->prepare(<<<'SQL'
            UPDATE livres
            SET titre = :titre,
                auteur = :auteur,
                isbn = :isbn,
                annee_publication = :annee
            WHERE id = :id
        SQL);
        try {
            $statement->execute([
                'id' => $livre->id,
                'titre' => $livre->titre,
                'auteur' => $livre->auteur,
                'isbn' => $livre->isbn,
                'annee' => $livre->anneePublication,
            ]);
        } catch (PDOException $exception) {
            $this->convertirContrainteIsbn($exception);
        }

        return $statement->rowCount() > 0;
    }

    public function supprimer(int $id): bool
    {
        $history = $this->pdo->prepare('SELECT COUNT(*) FROM emprunts WHERE livre_id = :id');
        $history->execute(['id' => $id]);
        if ((int) $history->fetchColumn() > 0) {
            throw new LivreAvecHistoriqueException(
                "Un livre possédant un historique d'emprunt ne peut pas être supprimé."
            );
        }

        $statement = $this->pdo->prepare('DELETE FROM livres WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /** @param array<string,mixed> $row */
    private function hydrate(array $row): Livre
    {
        return new Livre(
            (int) $row['id'],
            (string) $row['titre'],
            (string) $row['auteur'],
            $row['isbn'] === null ? null : (string) $row['isbn'],
            $row['annee_publication'] === null ? null : (int) $row['annee_publication'],
            (int) $row['disponible'] === 1,
        );
    }

    private function echapperLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function convertirContrainteIsbn(PDOException $exception): never
    {
        if (str_contains($exception->getMessage(), 'UNIQUE constraint failed: livres.isbn')) {
            throw new IsbnDejaUtiliseException('Cet ISBN est déjà utilisé par un autre livre.', 0, $exception);
        }

        throw $exception;
    }
}
