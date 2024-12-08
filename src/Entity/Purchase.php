<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $buyer;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(mappedBy: 'purchase', targetEntity: PurchaseProduct::class, cascade: ['persist', 'remove'])]
    private Collection $purchaseProducts;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->purchaseProducts = new ArrayCollection();
    }

// Getters et setters...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(User $buyer): self
    {
        $this->buyer = $buyer;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getPurchaseProducts(): Collection
    {
        return $this->purchaseProducts;
    }

    public function addPurchaseProduct(PurchaseProduct $purchaseProduct): self
    {
        if (!$this->purchaseProducts->contains($purchaseProduct)) {
            $this->purchaseProducts[] = $purchaseProduct;
            $purchaseProduct->setPurchase($this);
        }
        return $this;
    }

    public function removePurchaseProduct(PurchaseProduct $purchaseProduct): self
    {
        if ($this->purchaseProducts->removeElement($purchaseProduct)) {
            if ($purchaseProduct->getPurchase() === $this) {
                $purchaseProduct->setPurchase(null);
            }
        }
        return $this;
    }
}
