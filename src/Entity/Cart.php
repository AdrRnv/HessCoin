<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'carts')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column]
    private float $totalPrice = 0;

    #[ORM\OneToMany(targetEntity: CartProduct::class, mappedBy: 'carts')]
    private Collection $cartProducts;

    public function __construct()
    {
        $this->cartProducts = new ArrayCollection();
    }

    public function getCartProducts(): Collection
    {
        return $this->cartProducts;
    }

    public function setCartProducts(Collection $cartProducts): void
    {
        $this->cartProducts = $cartProducts;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

}
