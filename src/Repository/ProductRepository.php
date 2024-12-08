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

    public function findFilteredProducts(?string $search, ?string $categoryFilter, ?string $locationFilter, ?float $minPrice, ?float $maxPrice): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('p.status = :status')
            ->setParameter('status', Product::STATUS_AVAILABLE);

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

        if ($minPrice) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }


        return $qb->getQuery()->getResult();
    }
}
