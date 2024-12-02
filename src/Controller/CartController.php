<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route('/cart')]
class CartController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }

    #[Route('/', name: 'app_product_index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/list', name: 'app_cart_list')]
    public function list(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findBy(['user' => $user]);
        $cartProducts = $entityManager->getRepository(CartProduct::class)->findBy(['cart' => $cart]);

        return $this->render('cart/list.html.twig', [
            'cartProducts' => $cartProducts,
        ]);
    }

    #[Route('/add/product/{id}', name: 'app_cart_add')]
    public function add(Request $request, ?int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        $cartProduct = new CartProduct();
        $cartProduct->setCart($cart);
        $cartProduct->setProducts($product);
        $cartProduct->setQuantity(1);


        $this->entityManager->persist($cartProduct);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/delete/{id}', name: 'app_cart_delete')]
    public function delete(Request $request, int $id): Response
    {
        $cartItem = $this->entityManager->getRepository(CartProduct::class)->find($id);
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_cart_list');
    }
}
