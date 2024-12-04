<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findFilteredProducts(?string $search, ?string $categoryFilter, ?string $locationFilter): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c');

        if ($search) {
            $qb->andWhere('LOWER(p.title) LIKE LOWER(:search)')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryFilter) {
            $qb->andWhere('c.name = :category')
                ->setParameter('category', $categoryFilter);
        }

        if ($locationFilter) {
            $qb->andWhere('p.postalCode LIKE :location')
                ->setParameter('location', '%' . $locationFilter . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
