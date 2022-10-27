<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{

    #[Route('/{slug}', name: 'list')]
    public function list(Categorie $category, ProductRepository $productRepository, Request $request): Response
    {   
        //recuperation du numéro de page dans l'url
        $page = $request->query->getInt('page', 1);
        // recup les categories et les produits de la catégorie
        $products = $productRepository->findProductsPaginated($page, $category->getSlug(), 3);

        return $this->render('categories/list.html.twig', compact('category', 'products'));  
        
       /*  // ecris autrement
        return $this->render('categories/list.html.twig', [
            'category' => $category,
            'products' => $products
        ]);   */
    }
}