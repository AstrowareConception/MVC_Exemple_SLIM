<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Emprunt
{
    public ?int $id;
    public int $livreId;
    public string $emprunteurNom;
    public string $emprunteurEmail;
    public DateTimeImmutable $emprunteLe;
    public DateTimeImmutable $echeanceLe;
    public ?DateTimeImmutable $retourneLe;

    public function __construct(
        ?int $id,
        int $livreId,
        string $emprunteurNom,
        string $emprunteurEmail,
        DateTimeImmutable $emprunteLe,
        DateTimeImmutable $echeanceLe,
        ?DateTimeImmutable $retourneLe = null,
    ) {
        $emprunteurNom = trim($emprunteurNom);
        $emprunteurEmail = trim($emprunteurEmail);

        if ($livreId < 1) {
            throw new InvalidArgumentException("L'identifiant du livre est invalide.");
        }
        if (mb_strlen(trim($emprunteurNom), 'UTF-8') < 2 || mb_strlen(trim($emprunteurNom), 'UTF-8') > 120) {
            throw new InvalidArgumentException("Le nom de l'emprunteur doit contenir entre 2 et 120 caractères.");
        }
        if (filter_var($emprunteurEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException("L'adresse électronique de l'emprunteur est invalide.");
        }
        if ($echeanceLe <= $emprunteLe) {
            throw new InvalidArgumentException("L'échéance doit être postérieure à la date d'emprunt.");
        }

        $this->id = $id;
        $this->livreId = $livreId;
        $this->emprunteurNom = $emprunteurNom;
        $this->emprunteurEmail = $emprunteurEmail;
        $this->emprunteLe = $emprunteLe;
        $this->echeanceLe = $echeanceLe;
        $this->retourneLe = $retourneLe;
    }

    /** @return array<string,int|string|null> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'livre_id' => $this->livreId,
            'emprunteur_nom' => $this->emprunteurNom,
            'emprunteur_email' => $this->emprunteurEmail,
            'emprunte_le' => $this->emprunteLe->format('Y-m-d'),
            'echeance_le' => $this->echeanceLe->format('Y-m-d'),
            'retourne_le' => $this->retourneLe?->format('Y-m-d'),
        ];
    }
}
