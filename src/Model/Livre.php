<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

/**
 * Entité du domaine : elle protège les invariants d'un livre quel que soit
 * le canal d'entrée (formulaire HTML, API JSON, import ou test automatisé).
 */
final readonly class Livre
{
    public ?int $id;
    public string $titre;
    public string $auteur;
    public ?string $isbn;
    public ?int $anneePublication;
    public bool $disponible;

    public function __construct(
        ?int $id,
        string $titre,
        string $auteur,
        ?string $isbn = null,
        ?int $anneePublication = null,
        bool $disponible = true,
    ) {
        $titre = trim($titre);
        $auteur = trim($auteur);
        $isbn = $isbn === null ? null : strtoupper(str_replace([' ', '-'], '', trim($isbn)));
        $isbn = $isbn === '' ? null : $isbn;

        if ($titre === '' || mb_strlen($titre, 'UTF-8') > 120) {
            throw new InvalidArgumentException('Le titre est obligatoire et limité à 120 caractères.');
        }
        if ($auteur === '' || mb_strlen($auteur, 'UTF-8') > 120) {
            throw new InvalidArgumentException("L'auteur est obligatoire et limité à 120 caractères.");
        }
        if ($isbn !== null && preg_match('/^(?:[0-9]{9}[0-9X]|[0-9]{13})$/', $isbn) !== 1) {
            throw new InvalidArgumentException("L'ISBN doit contenir 10 ou 13 caractères valides.");
        }
        $maximum = (int) date('Y') + 2;
        if ($anneePublication !== null && ($anneePublication < 1450 || $anneePublication > $maximum)) {
            throw new InvalidArgumentException("L'année de publication doit être comprise entre 1450 et {$maximum}.");
        }

        $this->id = $id;
        $this->titre = $titre;
        $this->auteur = $auteur;
        $this->isbn = $isbn;
        $this->anneePublication = $anneePublication;
        $this->disponible = $disponible;
    }

    /** @return array{id:?int,titre:string,auteur:string,isbn:?string,annee_publication:?int,disponible:bool} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'auteur' => $this->auteur,
            'isbn' => $this->isbn,
            'annee_publication' => $this->anneePublication,
            'disponible' => $this->disponible,
        ];
    }
}
