<?php

namespace App\Tests\Unit\Service;

use App\Entity\Article;
use App\Entity\Dossier;
use App\Entity\Emplacement;
use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Service\DossierContext;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * StockService::adjust() est le point de passage unique de tout mouvement de stock.
 */
final class StockServiceTest extends TestCase
{
    private StockRepository&MockObject $repo;
    private EntityManagerInterface&MockObject $em;
    private DossierContext&MockObject $dossierContext;
    private StockService $service;

    protected function setUp(): void
    {
        $this->repo           = $this->createMock(StockRepository::class);
        $this->em             = $this->createMock(EntityManagerInterface::class);
        $this->dossierContext = $this->createMock(DossierContext::class);
        $this->service        = new StockService($this->repo, $this->em, $this->dossierContext);
    }

    private function article(): Article
    {
        return (new Article())->setReference('ART-1')->setLibelle('Article');
    }

    private function emplacement(): Emplacement
    {
        return (new Emplacement())->setCode('A-01');
    }

    private function stockExistant(int $quantite): Stock
    {
        $stock = (new Stock())->setArticle($this->article())->setEmplacement($this->emplacement())->setQuantite($quantite);
        $this->repo->method('findOneBy')->willReturn($stock);

        return $stock;
    }

    public function testCreeLeStockAbsentEtLeRattacheAuDossierCourant(): void
    {
        $dossier = (new Dossier())->setCode('D1')->setRaisonSociale('Dossier 1');
        $this->repo->method('findOneBy')->willReturn(null);
        $this->dossierContext->expects(self::once())->method('getCurrentOrThrow')->willReturn($dossier);
        $this->em->expects(self::once())->method('persist')->with(self::isInstanceOf(Stock::class));

        $stock = $this->service->adjust($this->article(), $this->emplacement(), 5);

        self::assertSame(5, $stock->getQuantite());
        self::assertSame($dossier, $stock->getDossier());
    }

    public function testRechercheLeStockParArticleEtEmplacement(): void
    {
        $article     = $this->article();
        $emplacement = $this->emplacement();
        $this->repo->expects(self::once())
            ->method('findOneBy')
            ->with(['article' => $article, 'emplacement' => $emplacement])
            ->willReturn((new Stock())->setQuantite(1));

        $this->service->adjust($article, $emplacement, 1);
    }

    public function testIncrementeUnStockExistantSansLePersisterDeNouveau(): void
    {
        $stock = $this->stockExistant(3);
        $this->em->expects(self::never())->method('persist');

        $this->service->adjust($this->article(), $this->emplacement(), 4);

        self::assertSame(7, $stock->getQuantite());
    }

    public function testDecrementeUnStockExistant(): void
    {
        $stock = $this->stockExistant(10);

        $this->service->adjust($this->article(), $this->emplacement(), -4);

        self::assertSame(6, $stock->getQuantite());
    }

    public function testAutoriseUnStockRameneAZero(): void
    {
        $stock = $this->stockExistant(4);

        $this->service->adjust($this->article(), $this->emplacement(), -4);

        self::assertSame(0, $stock->getQuantite());
    }

    public function testRefuseUnStockNegatifAvantToutEcriture(): void
    {
        $stock = $this->stockExistant(3);

        try {
            $this->service->adjust($this->article(), $this->emplacement(), -5);
            self::fail('Une DomainException était attendue.');
        } catch (\DomainException $e) {
            self::assertStringContainsString('Stock insuffisant', $e->getMessage());
            self::assertStringContainsString('disponible: 3', $e->getMessage());
        }

        self::assertSame(3, $stock->getQuantite(), 'La quantité ne doit pas être modifiée.');
    }

    public function testRefuseUneSortieSurUnStockInexistantSansCreerDeStock(): void
    {
        $this->repo->method('findOneBy')->willReturn(null);
        $this->dossierContext->expects(self::never())->method('getCurrentOrThrow');
        $this->em->expects(self::never())->method('persist');

        $this->expectExceptionMessage('disponible: 0, demandé: 1');

        $this->service->adjust($this->article(), $this->emplacement(), -1);
    }

    public function testDeuxMouvementsSurUnMemeStockNouveauNeCreentQuUnSeulStock(): void
    {
        // Réception à plusieurs lignes pour le même article et le même emplacement :
        // le stock créé par la première ligne n'est pas encore en base à la seconde.
        $article     = $this->article();
        $emplacement = $this->emplacement();
        $this->repo->method('findOneBy')->willReturn(null);
        $this->dossierContext->method('getCurrentOrThrow')->willReturn((new Dossier())->setCode('D1')->setRaisonSociale('D1'));
        $this->em->expects(self::once())->method('persist');

        $premier = $this->service->adjust($article, $emplacement, 1);
        $second  = $this->service->adjust($article, $emplacement, 2);

        self::assertSame($premier, $second);
        self::assertSame(3, $second->getQuantite());
    }

    public function testNeValideJamaisLaTransactionLuiMeme(): void
    {
        // L'appelant décide du flush : un transfert compose sortie + entrée dans une seule transaction.
        $this->stockExistant(10);
        $this->em->expects(self::never())->method('flush');

        $this->service->adjust($this->article(), $this->emplacement(), -2);
    }
}
