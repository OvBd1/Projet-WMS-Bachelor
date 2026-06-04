<?php

namespace App\Service;

use App\DTO\ArticleDTO;
use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use App\Repository\TypeConditionnementRepository;
use Doctrine\ORM\EntityManagerInterface;

class ArticleService
{
    public function __construct(
        private ArticleRepository $repo,
        private TypeConditionnementRepository $typeCondRepo,
        private EntityManagerInterface $em
    ) {}

    public function create(ArticleDTO $dto, ?Utilisateur $user = null): Article
    {
        if ($this->repo->findOneBy(['reference' => $dto->reference])) {
            throw new \DomainException('Une référence identique existe déjà.');
        }

        $article = new Article();
        $this->hydrate($article, $dto);
        if ($user) {
            $article->setCreatedBy($user);
        }
        $this->em->persist($article);
        $this->em->flush();

        return $article;
    }

    public function update(Article $article, ArticleDTO $dto, ?Utilisateur $user = null): Article
    {
        $existing = $this->repo->findOneBy(['reference' => $dto->reference]);
        if ($existing && $existing->getId() !== $article->getId()) {
            throw new \DomainException('Une référence identique existe déjà.');
        }

        $this->hydrate($article, $dto);
        if ($user) {
            $article->setUpdatedBy($user);
        }
        $this->em->flush();

        return $article;
    }

    public function delete(Article $article): void
    {
        $this->em->remove($article);
        $this->em->flush();
    }

    public function setImage(Article $article, string $filename): void
    {
        $article->setImagePath($filename);
        $this->em->flush();
    }

    private function hydrate(Article $article, ArticleDTO $dto): void
    {
        $article->setReference($dto->reference)
                ->setLibelle($dto->libelle)
                ->setDescription($dto->description)
                ->setGestionDlc($dto->gestionDlc)
                ->setGestionNumeroSerie($dto->gestionNumeroSerie);

        $typeConditionnement = $dto->typeConditionnementId
            ? $this->typeCondRepo->find($dto->typeConditionnementId)
            : null;
        $article->setTypeConditionnement($typeConditionnement);
    }

    public function normalize(Article $a, bool $withStocks = false): array
    {
        $tc = $a->getTypeConditionnement();
        $data = [
            'id'                  => $a->getId(),
            'reference'           => $a->getReference(),
            'libelle'             => $a->getLibelle(),
            'description'         => $a->getDescription(),
            'gestionDlc'          => $a->isGestionDlc(),
            'gestionNumeroSerie'  => $a->isGestionNumeroSerie(),
            'typeConditionnement' => $tc ? ['id' => $tc->getId(), 'libelle' => $tc->getLibelle()] : null,
            'imagePath'           => $a->getImagePath(),
        ];

        if ($withStocks) {
            $data['stocks'] = array_map(fn($s) => [
                'id'          => $s->getId(),
                'quantite'    => $s->getQuantite(),
                'emplacement' => [
                    'id'   => $s->getEmplacement()->getId(),
                    'code' => $s->getEmplacement()->getCode(),
                ],
            ], $a->getStocks()->toArray());
        }

        return $data;
    }
}
