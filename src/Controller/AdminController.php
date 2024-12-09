<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Purchase;
use App\Entity\User;
use App\Service\ApiCategoryService;
use App\Service\ApiProductService;
use App\Service\ApiUserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AdminController extends AbstractController
{
    private ApiProductService $apiProductService;
    private ApiUserService $apiUserService;

    private ApiCategoryService $apiCategoryService;
    private SerializerInterface $serializer;

    public function __construct(ApiProductService $apiProductService, ApiUserService $apiUserService,ApiCategoryService $apiCategoryService,SerializerInterface $serializer,)
    {
        $this->apiProductService = $apiProductService;
        $this->apiUserService = $apiUserService;
        $this->apiCategoryService = $apiCategoryService;
        $this->serializer = $serializer;
    }

    #[Route('/admin',name: 'admin_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $purchases = $entityManager->getRepository(Purchase::class)->findAll();
        return $this->render('admin/index.html.twig', [
            'purchases' => $purchases,
        ]);
    }

    #[Route('/importProductsApi',name: 'importProductsApi')]
    public function importProductsApi(EntityManagerInterface $entityManager): Response
    {
        $products = $this->apiProductService->fetchProductsFromApi();
        $users = $entityManager->getRepository(User::class)->findAll();
        if(empty($users)){
            $this->addFlash('error', 'aucun autilisateur trouvé');
        }
        if(!empty($products)) {
            foreach ($products as $product){
                $apiUser =$entityManager->getRepository(User::class)->findOneBy(['email' => 'apiuser@gmail.com']);
                $category =$entityManager->getRepository(Category::class)->findOneBy(['name' => $product['category']]);
                $newProduct = new Product();
                $newProduct->setTitle($product['title']);
                $newProduct->setDescription($product['description']);
                $newProduct->setPrice($product['price']);
                $newProduct->setImageName($product['thumbnail']);
                $newProduct->setUser($apiUser);
                $newProduct->setCategory($category);
                $newProduct->setPostalCode('01000');
                $entityManager->persist($newProduct);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Products imported successfully');
        }else{
            $this->addFlash('error', 'Failed to import products');
        }
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/importUsersApi',name: 'importUsersApi')]
    public function importUsersApi(EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $users = $this->apiUserService->fetchUsersFromApi();
        $apiUser = $entityManager->getRepository(User::class)->findOneBy(['username' => 'ApiUser']);
        if(!$apiUser){
            $apiUser = new User();
            $apiUser->setEmail('apiuser@gmail.com');
            $apiUser->setFirstName('Api');
            $apiUser->setLastName('User');
            $apiUser->setUsername('ApiUser');
            $apiUser->setPhone('+33 678 986 534');
            $apiUser->setPassword($userPasswordHasher->hashPassword($apiUser,'apiUserAdmin'));
            $entityManager->persist($apiUser);
            $entityManager->flush();
        }
        if (!empty($users)){
            foreach ($users as $user){
                $newUser = new User();
                $newUser = $this->serializer->deserialize(json_encode($user), User::class, 'json');
                $newUser->setPassword($userPasswordHasher->hashPassword($newUser,$user['password'] ));
                $newUser->setImageName($user['image']);
                $entityManager->persist($newUser);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Users imported successfully');
        }else{
            $this->addFlash('error','Failed to import users');
        }
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/importCategoriesApi',name: 'importCategoriesApi')]
    public function importCategoriesApi(EntityManagerInterface $entityManager): Response
    {
        $categories = $this->apiCategoryService->fetchCategoriesFromApi();
        if(!empty($categories)){
            foreach ($categories as $category) {
                $newCategory = new Category();
                $newCategory->setName($category['name']);
                $entityManager->persist($newCategory);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Categories imported successfully');
        }else{
            $this->addFlash('error','Failed to import categories');
        }
        return $this->redirectToRoute('admin_dashboard');
    }
}
