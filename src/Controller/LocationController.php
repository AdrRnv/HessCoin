<?php

namespace App\Controller;

use App\Form\LocationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/set-location', name: 'app_set_location')]
    public function setLocation(Request $request): Response
    {
        $form = $this->createForm(LocationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $location = $data['location'];

            $session = $this->requestStack->getSession();
            $session->set('location', $location);

            $this->addFlash('success', 'Postal code saved successfully!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('location/set_location.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
