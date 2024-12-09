<?php

namespace App\EventListener;

use App\Entity\CartProduct;
use App\Entity\Product;
use App\Entity\PurchaseProduct;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ProductListener
{
    public function preRemove(Product $product, LifecycleEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();

        $relatedEntities = $entityManager->getRepository(CartProduct::class)
            ->findBy(['product' => $product]);

        foreach ($relatedEntities as $entity) {
            $entityManager->remove($entity);
        }

        $relatedEntities = $entityManager->getRepository(PurchaseProduct::class)
            ->findBy(['product' => $product]);

        foreach ($relatedEntities as $entity) {
            $entityManager->remove($entity);
        }
    }
}
