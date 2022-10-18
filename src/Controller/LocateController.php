<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocateController extends AbstractController
{
    #[Route('/locate', name: 'app_locate')]
    public function index(): Response
    {
        return $this->render('locate/index.html.twig', [
            'controller_name' => 'LocateController',
        ]);
    }
}
