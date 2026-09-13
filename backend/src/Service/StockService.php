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

    /**
     * Point de passage unique de tout mouvement de stock.
     *
     * Vérifie avant d'écrire : si la quantité résultante est négative, lève une DomainException
     * avant toute modification (aucun stock créé, aucune quantité changée).
     * Ne fait pas le flush : l'appelant décide de la transaction, ce qui permet de composer
     * plusieurs mouvements (sortie + entrée d'un transfert).
     */
    public function adjust(Article $article, Emplacement $emplacement, int $delta): Stock
    {
        $stock = $this->stockRepo->findOneBy([
            'article'     => $article,
            'emplacement' => $emplacement,
        ]);

        $disponible = $stock?->getQuantite() ?? 0;
        $newQty     = $disponible + $delta;
        if ($newQty < 0) {
            throw new \DomainException(sprintf(
                'Stock insuffisant pour l\'article "%s" à l\'emplacement "%s" (disponible: %d, demandé: %d).',
                $article->getReference(),
                $emplacement->getCode(),
                $disponible,
                abs($delta)
            ));
        }

        if (!$stock) {
            $stock = new Stock();
            $stock->setArticle($article)->setEmplacement($emplacement);
            $stock->setDossier($this->dossierContext->getCurrentOrThrow());
            $this->em->persist($stock);
        }

        $stock->setQuantite($newQty);

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
