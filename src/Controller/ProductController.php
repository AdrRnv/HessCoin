<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use App\Form\ProductType;
use App\Repository\FavoriteRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
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

    #[Route('/list', name: 'app_product_list')]
    public function list(EntityManagerInterface $entityManager, Request $request): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        $user = $this->getUser();

        $favoriteProductIds = [];
        if ($user) {
            $userFavorites = $entityManager->getRepository(Favorite::class)->findBy(['user' => $user]);

            $favoriteProductIds = array_map(function ($favorite) {
                return $favorite->getProduct()->getId();
            }, $userFavorites);
        }
        $search = $request->query->get('search', '');
        $categoryFilter = $request->query->get('category', '');

        $categories = $entityManager->getRepository(Category::class)->findAll();
        $productsByCategory = [];

        foreach ($categories as $category) {
            if (!empty($categoryFilter) && $category->getId() != $categoryFilter) {
                continue;
            }

            $filteredProducts = [];
            foreach ($category->getProducts() as $product) {
                if (empty($search) || stripos($product->getTitle(), $search) !== false) {
                    $filteredProducts[] = $product;
                }
            }

            if (count($filteredProducts) > 0) {
                $productsByCategory[$category->getName()] = $filteredProducts;
            }
        }

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'favoriteProductIds' => $favoriteProductIds,
            'productsByCategory' => $productsByCategory,
            'categories' => $categories,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
        ]);
    }

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

    #[Route('/delete/{id}', name: 'app_product_delete')]
    public function delete(Request $request, int $id): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        $this->entityManager->remove($product);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/product/{id}/add_favorite', name: 'add_favorite')]
    public function addFavorite(int $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $user = $this->getUser();
        $favorite = new Favorite();
        $favorite->setUser($user);
        $favorite->setProduct($product);

        $entityManager->persist($favorite);
        $product->setLikesCount($product->getLikesCount() + 1);
        $entityManager->flush();

        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/product/{id}/remove_favorite', name: 'remove_favorite')]
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
            $entityManager->flush();
            $product->setLikesCount($product->getLikesCount() - 1);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_list');
    }

    #[Route('/product/{id}', name: 'product_show')]
    public function show(int $id,ProductRepository $productRepository):Response
    {
        $product = $productRepository->find($id);

        if(!$product){
            return $this->redirectToRoute('app_product_list');
        }

        return $this->render('product/show.html.twig',[
            'product' => $product,
        ]);
    }
}
