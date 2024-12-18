<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Category;
use App\Entity\Favorite;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Repository\FavoriteRepository;
use App\Repository\ProductRepository;
use App\Service\FavoriteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
class ProductController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FavoriteService $favoriteService,
    ){
    }

    #[Route('/', name: 'app_product_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/list', name: 'app_product_list')]
    public function list(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        $favoriteProductIds = [];
        if ($user) {
            $userFavorites = $entityManager->getRepository(Favorite::class)->findBy(['user' => $user]);

            $favoriteProductIds = array_map(static function ($favorite) {
                return $favorite->getProduct()?->getId();
            }, $userFavorites);
        }
        $search = $request->query->get('search');
        $categoryFilter = $request->query->get('category');
        $locationFilter = $request->getSession()->get('location');
        $minPrice = $request->query->get('min_price');
        $maxPrice = $request->query->get('max_price');

        $categories = $entityManager->getRepository(Category::class)->findAll();

        $minPrice = $minPrice !== null ? (float)$minPrice : null;
        $maxPrice = $maxPrice !== null ? (float)$maxPrice : null;

        $filteredProducts = $this->entityManager->getRepository(Product::class)->findFilteredProducts($search, $categoryFilter, $locationFilter, $minPrice, $maxPrice);
        $productsByCategory = [];

        foreach ($categories as $category) {
            $filteredCategoryProducts = array_filter($filteredProducts, function ($product) use ($category) {
                return $product->getCategory() === $category;
            });

            if (count($filteredCategoryProducts) > 0) {
                $productsByCategory[$category->getName()] = $filteredCategoryProducts;
            }
        }

        return $this->render('product/list.html.twig', [
            'products' => $filteredProducts,
            'favoriteProductIds' => $favoriteProductIds,
            'productsByCategory' => $productsByCategory,
            'categories' => $categories,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'locationFilter' => $locationFilter,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/add', name: 'app_product_add')]
    #[Route('/edit/{id}', name: 'app_product_edit')]
    public function add(Request $request, ?int $id): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($id) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);
        } else {
            $product = new Product();
        }
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUser($user);
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_product_list');
        }

        return $this->render('product/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/delete/{id}', name: 'app_product_delete')]
    public function delete(int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_login');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/cart/{id}', name: 'app_product_cart')]
    public function addToCart(int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $this->getUser()]);

        $cartProduct = new CartProduct();
        $cartProduct->setCart($cart);
        $cartProduct->setProduct($product);

        $this->entityManager->persist($cartProduct);

        $this->entityManager->flush();
        return $this->redirectToRoute('app_product_list');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/add_favorite', name: 'add_favorite')]
    public function addFavorite(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        $favorite = $this->favoriteService->createFavorite($user, $product);
        $entityManager->persist($favorite);

        $product->getCategory()->incrementTotalLikes();
        $product->incrementLikes();

        $entityManager->persist($product);

        $entityManager->flush();

        return $this->redirectToRoute('app_product_list');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/remove_favorite', name: 'remove_favorite')]
    public function removeFavorite(int $id, ProductRepository $productRepository, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $user = $this->getUser();

        $favorite = $favoriteRepository->findOneBy(['user' => $user, 'product' => $product]);

        if ($favorite) {
            $entityManager->remove($favorite);
            $product->incrementLikes(-1);
            $product->getCategory()->incrementTotalLikes(-1);
            $entityManager->persist($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/{id}', name: 'product_show')]
    public function show(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            return $this->redirectToRoute('app_product_list');
        }
        $product->incrementViews();
        $entityManager->flush();
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
