<?php

namespace App\Controller;

use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{

    #[Route('/{slug}', name: 'list')]
    public function list(Categorie $category): Response
    {   
        // recup les categories et les produits de la catégorie
        $products = $category->getProducts();        

        return $this->render('categories/list.html.twig', compact('category', 'products'));  
        
       /*  // ecris autrement
        return $this->render('categories/list.html.twig', [
            'category' => $category,
            'products' => $products
        ]);   */
    }
}