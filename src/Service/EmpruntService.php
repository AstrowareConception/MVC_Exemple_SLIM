<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Emprunt;
use App\Model\Exception\EmpruntDejaRetourneException;
use App\Model\Exception\EmpruntIntrouvableException;
use App\Model\Exception\LivreIndisponibleException;
use App\Model\Exception\LivreIntrouvableException;
use App\Repository\EmpruntRepository;
use App\Repository\LivreRepository;
use DateTimeImmutable;
use RuntimeException;

/**
 * Orchestre un cas d'utilisation impliquant plusieurs objets.
 *
 * Le contrôleur reste centré sur HTTP ; les repositories restent centrés sur
 * SQL. Le service exprime ici les étapes métier « emprunter » et « rendre ».
 */
final class EmpruntService
{
    public function __construct(
        private readonly LivreRepository $livres,
        private readonly EmpruntRepository $emprunts,
    ) {
    }

    public function emprunter(
        int $livreId,
        string $nom,
        string $email,
        ?DateTimeImmutable $date = null,
    ): Emprunt {
        $livre = $this->livres->trouver($livreId);
        if ($livre === null) {
            throw new LivreIntrouvableException('Livre introuvable.');
        }
        if (!$livre->disponible || $this->emprunts->actifPourLivre($livreId) !== null) {
            throw new LivreIndisponibleException('Ce livre est déjà emprunté.');
        }

        $date ??= new DateTimeImmutable('today');
        $emprunt = new Emprunt(
            null,
            $livreId,
            trim($nom),
            trim($email),
            $date,
            $date->modify('+21 days'),
        );
        $id = $this->emprunts->creer($emprunt);

        return $this->emprunts->trouver($id)
            ?? throw new RuntimeException("L'emprunt créé n'a pas pu être relu.");
    }

    public function retourner(int $empruntId, ?DateTimeImmutable $date = null): Emprunt
    {
        $emprunt = $this->emprunts->trouver($empruntId);
        if ($emprunt === null) {
            throw new EmpruntIntrouvableException('Emprunt introuvable.');
        }
        if ($emprunt->retourneLe !== null) {
            throw new EmpruntDejaRetourneException('Cet emprunt a déjà été clôturé.');
        }

        $this->emprunts->enregistrerRetour($empruntId, $date ?? new DateTimeImmutable('today'));

        return $this->emprunts->trouver($empruntId)
            ?? throw new RuntimeException("L'emprunt retourné n'a pas pu être relu.");
    }
}
