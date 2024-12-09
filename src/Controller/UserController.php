<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route('/userProfile', name: 'user_profile')]
    #[IsGranted('ROLE_USER')]
    public function userProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Récupération des produits de l'utilisateur
        $products = $entityManager->getRepository(Product::class)->findBy([
            'user' => $user,
        ]);

        // Création du formulaire pour le profil utilisateur
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
        ]);
    }
}
