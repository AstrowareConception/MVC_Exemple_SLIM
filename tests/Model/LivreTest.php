<?php

declare(strict_types=1);

namespace Tests\Model;

use App\Model\Livre;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LivreTest extends TestCase
{
    public function testNormaliseLesDonneesValides(): void
    {
        $livre = new Livre(null, '  Dune  ', ' Frank Herbert ', '978-2-266-32048-1', 1965);

        self::assertSame('Dune', $livre->titre);
        self::assertSame('Frank Herbert', $livre->auteur);
        self::assertSame('9782266320481', $livre->isbn);
        self::assertTrue($livre->disponible);
    }

    #[DataProvider('donneesInvalides')]
    public function testRefuseUneDonneeInvalide(string $titre, string $auteur, ?string $isbn, ?int $annee): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Livre(null, $titre, $auteur, $isbn, $annee);
    }

    /** @return iterable<string,array{string,string,?string,?int}> */
    public static function donneesInvalides(): iterable
    {
        yield 'titre vide' => ['', 'Autrice', null, 2020];
        yield 'auteur vide' => ['Titre', ' ', null, 2020];
        yield 'isbn mal formé' => ['Titre', 'Autrice', '1234', 2020];
        yield 'année avant Gutenberg' => ['Titre', 'Autrice', null, 1400];
    }
}
