<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('user/favorites',name: 'user_favorites')]
    public function favorites(): Response
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $favorites = $user->getFavorites();

        return $this->render('user/favorites.html.twig',[
            'favorites' => $favorites,
        ]);
    }
}
