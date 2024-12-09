<?php

namespace App\Service;

use App\Entity\Favorite;
use App\Entity\Product;
use App\Entity\User;

class FavoriteService
{
    public function createFavorite(User $user, Product $product): Favorite
    {
        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setProduct($product);

        return $favorite;
    }

}