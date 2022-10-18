<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products', name: 'products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'categories' => $categorieRepository->findBy([],['categoryOrder' => 'Asc'])
        ]);
    }

    #[Route('/{slug}', name: 'details')]
    public function details(Product $product): Response
    {        
        return $this->render('products/details.html.twig', compact('product'));        
    }
}