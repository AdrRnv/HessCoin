<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

    }

    #[Route('/', name: 'app_category_index')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/list', name: 'app_category_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('category/list.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/add', name: 'app_category_add')]
    #[Route('/edit/{id}', name: 'app_category_edit')]
    public function add(Request $request, ?int $id): Response
    {
        if ($id) {
            $category = $this->entityManager->getRepository(Category::class)->find($id);
        } else {
            $category = new Category();
        }
        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_category_list');
        }

        return $this->render('category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_category_delete')]
    public function delete(Request $request, int $id): Response
    {
        $category = $this->entityManager->getRepository(Category::class)->find($id);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_category_list');
    }
}
