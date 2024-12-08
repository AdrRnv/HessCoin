<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\PurchaseProduct;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderProduct;
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
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $this->addFlash('error', 'Panier introuvable');
            return $this->redirectToRoute('app_product_list');
        }

        $cartProducts = $entityManager->getRepository(CartProduct::class)->findBy(['cart' => $cart]);


        $totalPrice = array_reduce($cartProducts, function ($sum, CartProduct $cartProduct) {
            return $sum + $cartProduct->getProduct()->getPrice();
        }, 0);

        return $this->render('cart/list.html.twig', [
            'cartProducts' => $cartProducts,
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/add/product/{id}', name: 'app_cart_add')]
    public function add(Request $request, ?int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if(!$product){
            $this->addFlash('error','Produit introuvable');
            return $this->redirectToRoute('app_product_list');
        }

        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if(!$cart){
            $this->addFlash('error','Panier introuvable');
            return $this->redirectToRoute('app_product_list');
        }

        $productExist = $this->entityManager->getRepository(CartProduct::class)->findOneBy([
            'cart' => $cart->getId(),
            'product' => $product->getId()
            ]);
        if($productExist){
            $this->addFlash('error','Ce produit est déjà dans le panier');
        }else{
            $cartProduct = new CartProduct();
            $cartProduct->setProduct($product);
            $cartProduct->setCart($cart);
            $this->entityManager->persist($cartProduct);
            $this->entityManager->flush();

            $this->addFlash('succes',"Produit ajouté au panier avec succès.");
        }
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

    #[Route('/order', name: 'app_order')]
    public function createOrder(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart || $cart->getCartProducts()->isEmpty()) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_list');
        }

        $purchase = new Purchase();
        $purchase->setBuyer($user);

        foreach ($cart->getCartProducts() as $cartProduct) {
            /** @var Product $product */
            $product = $cartProduct->getProduct();

            $purchaseProduct = new PurchaseProduct($purchase, $product, $product->getPrice());
            $product->setStatus(Product::STATUS_SELL);
            $entityManager->persist($product);
            $entityManager->persist($purchaseProduct);

            $entityManager->remove($cartProduct);
        }

        $entityManager->persist($purchase);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commande a été enregistrée.');
        return $this->redirectToRoute('app_order_history');
    }




    #[Route('/delete-cartproduct/{product_id}', name: 'app_cart_delete')]
    public function deleteProduct(Request $request, $product_id, EntityManagerInterface $entityManager){
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            return $this->redirectToRoute('app_home');
        }
        $cartProduct = $entityManager->getRepository(CartProduct::class)
            ->findOneBy(['cart' => $cart, 'product' => $product_id]);

        if ($cartProduct) {
            $entityManager->remove($cartProduct);
            $product = $this->entityManager->getRepository(Product::class)->find($product_id);
            $product->setStatus(Product::STATUS_AVAILABLE);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_cart_list');
        } else {
            $this->addFlash('error', 'Produit non trouvé dans le panier.');
            return $this->redirectToRoute('app_cart_list');
        }
    }

    #[Route('/order/history', name: 'app_order_history')]
    public function purchaseHistory(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $purchases = $entityManager->getRepository(Purchase::class)->findBy(['buyer' => $user], ['createdAt' => 'DESC']);

        return $this->render('purchase/history.html.twig', [
            'purchases' => $purchases,
        ]);
    }
}
