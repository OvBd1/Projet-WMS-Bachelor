<?php

namespace App\Tests\Unit\Service;

use App\Entity\Article;
use App\Entity\Emplacement;
use App\Entity\LigneReception;
use App\Entity\Reception;
use App\Entity\Stock;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use App\Repository\TiersRepository;
use App\Service\DossierContext;
use App\Service\ReceptionService;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Cycle de vie d'une réception et effet sur le stock.
 */
final class ReceptionServiceTest extends TestCase
{
    private StockService&MockObject $stockService;
    private EntityManagerInterface&MockObject $em;
    private ReceptionService $service;

    protected function setUp(): void
    {
        $this->stockService = $this->createMock(StockService::class);
        $this->em           = $this->createMock(EntityManagerInterface::class);

        $this->service = new ReceptionService(
            $this->createMock(ArticleRepository::class),
            $this->createMock(EmplacementRepository::class),
            $this->createMock(TiersRepository::class),
            $this->stockService,
            $this->em,
            $this->createMock(DossierContext::class)
        );
    }

    /** @param list<int> $quantites */
    private function reception(string $statut, array $quantites = [5]): Reception
    {
        $reception = (new Reception())->setStatut($statut);
        foreach ($quantites as $i => $quantite) {
            $reception->addLigneReception((new LigneReception())
                ->setArticle((new Article())->setReference('ART-' . $i)->setLibelle('Article'))
                ->setEmplacement((new Emplacement())->setCode('A-0' . $i))
                ->setQuantite($quantite));
        }

        return $reception;
    }

    /** @return list<array{string, int}> */
    private function enregistrerMouvements(): \ArrayObject
    {
        $mouvements = new \ArrayObject();
        $this->stockService->method('adjust')->willReturnCallback(function (Article $a, Emplacement $e, int $delta) use ($mouvements) {
            $mouvements[] = [$a->getReference(), $delta];
            return new Stock();
        });

        return $mouvements;
    }

    public function testLaValidationAjusteLeStockDeChaqueLigne(): void
    {
        $mouvements = $this->enregistrerMouvements();
        $user       = new Utilisateur();
        $this->em->expects(self::once())->method('flush');

        $reception = $this->service->valider($this->reception(Reception::STATUT_EN_ATTENTE, [5, 3]), $user);

        self::assertSame([['ART-0', 5], ['ART-1', 3]], $mouvements->getArrayCopy());
        self::assertSame(Reception::STATUT_VALIDEE, $reception->getStatut());
        self::assertSame($user, $reception->getValidatedBy());
        self::assertNotNull($reception->getValidatedAt());
    }

    public function testSeuleUneReceptionEnAttentePeutEtreValidee(): void
    {
        $this->stockService->expects(self::never())->method('adjust');

        $this->expectExceptionMessage('Seules les réceptions en attente peuvent être validées.');

        $this->service->valider($this->reception(Reception::STATUT_VALIDEE), new Utilisateur());
    }

    public function testLAnnulationDUneReceptionValideeRetireLeStock(): void
    {
        $mouvements = $this->enregistrerMouvements();

        $reception = $this->service->annuler($this->reception(Reception::STATUT_VALIDEE, [5]));

        self::assertSame([['ART-0', -5]], $mouvements->getArrayCopy());
        self::assertSame(Reception::STATUT_ANNULEE, $reception->getStatut());
    }

    public function testLAnnulationDUneReceptionEnAttenteNeTouchePasAuStock(): void
    {
        $this->stockService->expects(self::never())->method('adjust');

        $reception = $this->service->annuler($this->reception(Reception::STATUT_EN_ATTENTE));

        self::assertSame(Reception::STATUT_ANNULEE, $reception->getStatut());
    }

    public function testUneReceptionDejaAnnuleeNePeutPasLEtreDeNouveau(): void
    {
        $this->expectExceptionMessage('Cette réception est déjà annulée.');

        $this->service->annuler($this->reception(Reception::STATUT_ANNULEE));
    }

    public function testUneReceptionValideeNePeutPasEtreSupprimee(): void
    {
        $this->em->expects(self::never())->method('remove');

        $this->expectException(\DomainException::class);

        $this->service->delete($this->reception(Reception::STATUT_VALIDEE));
    }
}
