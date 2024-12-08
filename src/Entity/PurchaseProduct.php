<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class PurchaseProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Purchase::class, inversedBy: 'purchaseProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private Purchase $purchase;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(type: 'float')]
    private float $price;

    public function __construct(Purchase $purchase, Product $product, float $price)
    {
        $this->purchase = $purchase;
        $this->product = $product;
        $this->price = $price;
    }

// Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(Purchase $purchase): self
    {
        $this->purchase = $purchase;
        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
}
