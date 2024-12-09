<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HomeController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $client = HttpClient::create([
            'headers' => [
                'User-Agent' => 'HessCoin App',
            ]
        ]);
        $client->request('GET', 'https://dummyjson.com/products');

        $categories = $this->entityManager->getRepository(Category::class)->findTopTwoCategoriesByLikes();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
        ]);
    }
}
