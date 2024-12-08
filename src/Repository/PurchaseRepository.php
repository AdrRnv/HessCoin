<?php

namespace App\Repository;

use App\Entity\Purchase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchase>
 *
 * @method Purchase|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchase|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchase[]    findAll()
 * @method Purchase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchase::class);
    }

    /**
     * Find purchases by a specific user.
     *
     * @param int $userId
     * @return Purchase[]
     */
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.buyer = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get the total revenue from all purchases.
     *
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return (float) $this->createQueryBuilder('p')
            ->select('SUM(pp.price)')
            ->innerJoin('p.purchaseProducts', 'pp')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get the number of products sold by all users.
     *
     * @return int
     */
    public function getTotalProductsSold(): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(pp.id)')
            ->innerJoin('p.purchaseProducts', 'pp')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
