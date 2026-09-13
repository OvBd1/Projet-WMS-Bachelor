<?php

namespace App\Tests\Unit\Doctrine;

use App\Doctrine\DossierFilter;
use App\Entity\Stock;
use App\Entity\Utilisateur;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\FilterCollection;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;
use PHPUnit\Framework\TestCase;

/**
 * Le cloisonnement est appliqué au niveau de la persistance, pour toute entité
 * qui utilise DossierScopedTrait.
 */
final class DossierFilterTest extends TestCase
{
    private function filtre(): DossierFilter
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('quote')->willReturnCallback(fn (string $v) => "'$v'");

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);
        $em->method('getFilters')->willReturn($this->createMock(FilterCollection::class));

        return new DossierFilter($em);
    }

    private function metadata(string $class): ClassMetadata
    {
        $metadata = new ClassMetadata($class);
        $metadata->initializeReflection(new RuntimeReflectionService());

        return $metadata;
    }

    public function testAjouteLaConditionDeDossierAUneEntiteCloisonnee(): void
    {
        $filtre = $this->filtre();
        $filtre->setParameter('dossierId', 3);

        self::assertSame("s0_.dossier_id = '3'", $filtre->addFilterConstraint($this->metadata(Stock::class), 's0_'));
    }

    public function testNeFiltrePasUneEntiteNonCloisonnee(): void
    {
        $filtre = $this->filtre();
        $filtre->setParameter('dossierId', 3);

        // L'utilisateur est rattaché à un dossier sans être cloisonné (un admin voit tous les comptes).
        self::assertSame('', $filtre->addFilterConstraint($this->metadata(Utilisateur::class), 'u0_'));
    }

    public function testNeFiltreRienTantQueLeDossierNEstPasResolu(): void
    {
        self::assertSame('', $this->filtre()->addFilterConstraint($this->metadata(Stock::class), 's0_'));
    }
}
