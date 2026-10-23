<?php

declare(strict_types=1);

namespace Tests\Repository;

use App\Model\Exception\IsbnDejaUtiliseException;
use App\Model\Livre;
use Tests\Support\DatabaseTestCase;

final class LivreRepositoryTest extends DatabaseTestCase
{
    public function testCreeEtRelitUnLivre(): void
    {
        $id = $this->livres->creer(new Livre(null, 'Dune', 'Frank Herbert', '9782266320481', 1965));
        $livre = $this->livres->trouver($id);

        self::assertNotNull($livre);
        self::assertSame('Dune', $livre->titre);
        self::assertTrue($livre->disponible);
    }

    public function testRechercheTitreOuAuteurSansTransformerPourcentEnJoker(): void
    {
        $this->livres->creer(new Livre(null, '100% architecture', 'Alice'));
        $this->livres->creer(new Livre(null, 'Architecture propre', 'Bob'));

        $resultats = $this->livres->tous('100%');

        self::assertCount(1, $resultats);
        self::assertSame('100% architecture', $resultats[0]->titre);
    }

    public function testRefuseDeuxLivresAvecLeMemeIsbn(): void
    {
        $this->livres->creer(new Livre(null, 'Premier', 'Autrice', '0306406152'));

        $this->expectException(IsbnDejaUtiliseException::class);
        $this->livres->creer(new Livre(null, 'Second', 'Auteur', '0-306-40615-2'));
    }
}
