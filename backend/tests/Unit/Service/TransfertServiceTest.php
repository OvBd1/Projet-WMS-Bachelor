<?php

namespace App\Tests\Unit\Service;

use App\DTO\TransfertDTO;
use App\Entity\Article;
use App\Entity\Dossier;
use App\Entity\Emplacement;
use App\Entity\TransfertEmplacement;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\EmplacementRepository;
use App\Service\DossierContext;
use App\Service\StockService;
use App\Service\TransfertService;
use App\Tests\Support\EntityIdTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TransfertServiceTest extends TestCase
{
    use EntityIdTrait;

    private ArticleRepository&MockObject $articleRepo;
    private EmplacementRepository&MockObject $emplacementRepo;
    private StockService&MockObject $stockService;
    private EntityManagerInterface&MockObject $em;
    private DossierContext&MockObject $dossierContext;
    private TransfertService $service;

    private Article $article;
    private Emplacement $source;
    private Emplacement $destination;

    protected function setUp(): void
    {
        $this->articleRepo     = $this->createMock(ArticleRepository::class);
        $this->emplacementRepo = $this->createMock(EmplacementRepository::class);
        $this->stockService    = $this->createMock(StockService::class);
        $this->em              = $this->createMock(EntityManagerInterface::class);
        $this->dossierContext  = $this->createMock(DossierContext::class);

        $this->service = new TransfertService(
            $this->articleRepo, $this->emplacementRepo, $this->stockService, $this->em, $this->dossierContext
        );

        $this->article     = $this->withId((new Article())->setReference('ART-1')->setLibelle('Article'), 1);
        $this->source      = $this->withId((new Emplacement())->setCode('A-01'), 10);
        $this->destination = $this->withId((new Emplacement())->setCode('B-01'), 20);
        $this->dossierContext->method('getCurrentOrThrow')->willReturn((new Dossier())->setCode('D1')->setRaisonSociale('D1'));
    }

    private function dto(int $sourceId = 10, int $destinationId = 20, int $quantite = 4): TransfertDTO
    {
        $dto = new TransfertDTO();
        $dto->articleId                = 1;
        $dto->emplacementSourceId      = $sourceId;
        $dto->emplacementDestinationId = $destinationId;
        $dto->quantite                 = $quantite;

        return $dto;
    }

    private function referentielComplet(): void
    {
        $this->articleRepo->method('find')->willReturn($this->article);
        $this->emplacementRepo->method('find')->willReturnCallback(fn (int $id) => match ($id) {
            10      => $this->source,
            20      => $this->destination,
            default => null,
        });
    }

    public function testDeplaceLaQuantiteParDeuxMouvementsDansUneSeuleTransaction(): void
    {
        $this->referentielComplet();
        $mouvements = [];
        $this->stockService->expects(self::exactly(2))->method('adjust')
            ->willReturnCallback(function (Article $a, Emplacement $e, int $delta) use (&$mouvements) {
                $mouvements[] = [$e->getCode(), $delta];
                return new \App\Entity\Stock();
            });
        $this->em->expects(self::once())->method('persist')->with(self::isInstanceOf(TransfertEmplacement::class));
        $this->em->expects(self::once())->method('flush');

        $transfert = $this->service->create($this->dto(), new Utilisateur());

        self::assertSame([['A-01', -4], ['B-01', 4]], $mouvements, 'Sortie de la source puis entrée en destination.');
        self::assertSame(4, $transfert->getQuantite());
        self::assertSame($this->source, $transfert->getEmplacementSource());
        self::assertSame($this->destination, $transfert->getEmplacementDestination());
    }

    public function testUnStockInsuffisantInterromptLeTransfertSansValidation(): void
    {
        $this->referentielComplet();
        $this->stockService->method('adjust')->willThrowException(new \DomainException('Stock insuffisant'));
        $this->em->expects(self::never())->method('flush');

        $this->expectException(\DomainException::class);

        $this->service->create($this->dto(), new Utilisateur());
    }

    public function testRefuseUnTransfertVersLeMemeEmplacement(): void
    {
        $this->referentielComplet();
        $this->stockService->expects(self::never())->method('adjust');

        $this->expectExceptionMessage("L'emplacement source et destination doivent être différents.");

        $this->service->create($this->dto(10, 10), new Utilisateur());
    }

    public function testRefuseUnArticleIntrouvable(): void
    {
        $this->articleRepo->method('find')->willReturn(null);
        $this->stockService->expects(self::never())->method('adjust');

        $this->expectExceptionMessage('Article introuvable.');

        $this->service->create($this->dto(), new Utilisateur());
    }

    public function testRefuseUnEmplacementSourceIntrouvable(): void
    {
        $this->referentielComplet();

        $this->expectExceptionMessage('Emplacement source introuvable.');

        $this->service->create($this->dto(99), new Utilisateur());
    }

    public function testRefuseUnEmplacementDestinationIntrouvable(): void
    {
        $this->referentielComplet();

        $this->expectExceptionMessage('Emplacement destination introuvable.');

        $this->service->create($this->dto(10, 99), new Utilisateur());
    }
}
