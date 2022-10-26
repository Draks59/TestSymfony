<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_main');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path:'/resetpassword', name:'app_request_reset_password')]
    public function requestResetPassword(
        Request $request,
        UserRepository $userRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mailer,
        ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            // Recupération de l'utilisateur grace a son e-mail
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            
            // On verifie si on a un utilisateur
            if($user){
                // Generation du token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetPassword($token);
                $entityManager->persist($user);
                $entityManager->flush();

                //Generation du lien
                $url = $this->generateUrl('app_reset_password', ['token' => $token],UrlGeneratorInterface::ABSOLUTE_URL);
                
                //Création des données du mail
                $context = compact('url', 'user');

                // envoi du mail

                $mailer->send(
                    'no-reply@zerveza.fr',
                    $user->getEmail(),
                    'Réinitialisation du mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'E-mail envoyé avec succès');
                return $this->redirectToRoute('app_main');

            }
            // si pas d'utilisateur
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_main');
        }
            
        return $this->render('security/reset_password_request.html.twig', [
            'requestPasswordForm' => $form->createView()
        ]);  
    }

    #[Route('/resetpassword/{token}', name:'app_reset_password')]
    public function resetPassword(
        ?string $token,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response
    {
        // Verifier si le token est valide dans la bdd
        $user = $userRepository->findOneByResetPassword($token);
        
        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                //On efface le token
                $user->setResetPassword('');
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_main');
            }

            return $this->render('security/reset_password.html.twig', [
                'passwordForm' => $form->createView()
            ]);

        }
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_main');
    }
}
