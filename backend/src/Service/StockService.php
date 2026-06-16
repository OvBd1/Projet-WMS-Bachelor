<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Emplacement;
use App\Entity\Stock;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    public function __construct(
        private StockRepository $stockRepo,
        private EntityManagerInterface $em,
        private DossierContext $dossierContext
    ) {}

    public function adjust(Article $article, Emplacement $emplacement, int $delta): Stock
    {
        $stock = $this->stockRepo->findOneBy([
            'article'     => $article,
            'emplacement' => $emplacement,
        ]);

        if (!$stock) {
            $stock = new Stock();
            $stock->setArticle($article)->setEmplacement($emplacement)->setQuantite(0);
            $stock->setDossier($this->dossierContext->getCurrentOrThrow());
            $this->em->persist($stock);
        }

        $newQty = $stock->getQuantite() + $delta;
        if ($newQty < 0) {
            throw new \DomainException(sprintf(
                'Stock insuffisant pour l\'article "%s" à l\'emplacement "%s" (disponible: %d, demandé: %d).',
                $article->getReference(),
                $emplacement->getCode(),
                $stock->getQuantite(),
                abs($delta)
            ));
        }

        $stock->setQuantite($newQty);

        return $stock;
    }

    public function setQuantite(Stock $stock, int $quantite): Stock
    {
        $stock->setQuantite($quantite);
        $this->em->flush();
        return $stock;
    }

    public function normalize(Stock $s): array
    {
        return [
            'id'       => $s->getId(),
            'quantite' => $s->getQuantite(),
            'article'  => [
                'id'        => $s->getArticle()->getId(),
                'reference' => $s->getArticle()->getReference(),
                'libelle'   => $s->getArticle()->getLibelle(),
            ],
            'emplacement' => [
                'id'   => $s->getEmplacement()->getId(),
                'code' => $s->getEmplacement()->getCode(),
            ],
        ];
    }
}
