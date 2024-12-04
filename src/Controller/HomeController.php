<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $client = HttpClient::create([
            'headers' => [
                'User-Agent' => 'HessCoin App',
            ]
        ]);
        $response = $client->request('GET','https://dummyjson.com/products');

        $categories = $this->entityManager->getRepository(Category::class)->findTopTwoCategoriesByLikes();
        $products = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $products[] = $category->getProducts();
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
        ]);
    }
}
