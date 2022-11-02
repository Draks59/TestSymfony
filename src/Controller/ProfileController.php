<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\UserEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil', name: 'app_profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('profile/orders.html.twig', [
            'controller_name' => 'ProfileControllerOrder',
        ]);
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function edit(User $user, Request $request,EntityManagerInterface $em ): Response
    {   
        if (!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }

        if($this->getUser() !== $user){
            return $this->redirectToRoute('app_profile_index');
        }

        $form = $this->createForm(UserEditFormType::class, $user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $em->persist($user);
            $em->flush();
            $this->addFlash(
                'success',
                'Les informations on bien était mise a jour'
            );
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
        
    }
}
