<?php

namespace App\Repository;

use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    /** Charge article + emplacement en un seul JOIN pour éviter le N+1. */
    public function findAllWithJoins(): array
    {
        return $this->createQueryBuilder('s')
            ->addSelect('a', 'e')
            ->join('s.article', 'a')
            ->join('s.emplacement', 'e')
            ->getQuery()
            ->getResult();
    }
}
