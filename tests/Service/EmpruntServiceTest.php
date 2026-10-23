<?php

declare(strict_types=1);

namespace Tests\Service;

use App\Model\Exception\EmpruntDejaRetourneException;
use App\Model\Exception\LivreIndisponibleException;
use App\Model\Livre;
use App\Service\EmpruntService;
use DateTimeImmutable;
use Tests\Support\DatabaseTestCase;

final class EmpruntServiceTest extends DatabaseTestCase
{
    private EmpruntService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmpruntService($this->livres, $this->emprunts);
    }

    public function testEmpruntePourVingtEtUnJoursPuisRendLeLivre(): void
    {
        $livreId = $this->livres->creer(new Livre(null, 'Dune', 'Frank Herbert'));
        $emprunt = $this->service->emprunter(
            $livreId,
            'Ada Lovelace',
            'ada@example.test',
            new DateTimeImmutable('2026-10-01'),
        );

        self::assertSame('2026-10-22', $emprunt->echeanceLe->format('Y-m-d'));
        self::assertFalse($this->livres->trouver($livreId)?->disponible);

        $retour = $this->service->retourner($emprunt->id ?? 0, new DateTimeImmutable('2026-10-10'));
        self::assertSame('2026-10-10', $retour->retourneLe?->format('Y-m-d'));
        self::assertTrue($this->livres->trouver($livreId)?->disponible);
    }

    public function testRefuseUnSecondEmpruntActif(): void
    {
        $livreId = $this->livres->creer(new Livre(null, 'Dune', 'Frank Herbert'));
        $this->service->emprunter($livreId, 'Ada Lovelace', 'ada@example.test');

        $this->expectException(LivreIndisponibleException::class);
        $this->service->emprunter($livreId, 'Grace Hopper', 'grace@example.test');
    }

    public function testRefuseUnSecondRetour(): void
    {
        $livreId = $this->livres->creer(new Livre(null, 'Dune', 'Frank Herbert'));
        $emprunt = $this->service->emprunter($livreId, 'Ada Lovelace', 'ada@example.test');
        $this->service->retourner($emprunt->id ?? 0);

        $this->expectException(EmpruntDejaRetourneException::class);
        $this->service->retourner($emprunt->id ?? 0);
    }
}
