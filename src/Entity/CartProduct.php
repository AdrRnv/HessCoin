<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Repository\CartProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartProductRepository::class)]
class CartProduct
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private Cart $cart;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartProduct')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column]
    private int $quantity;

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProducts(Product $products): void
    {
        $this->products = $products;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
    }

}
