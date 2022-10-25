<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            // Generation du JWT 
            // Création du header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            // Création du payload
            $payload = [
                'user_id' => $user->getId()
            ];
            // Generation du token
            $token = $jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));

            //on envoi un mail

            $mail->send(
                'no-replay@zerveza.net',
                $user->getEmail(),
                'Activation de votre compte Zerveza',
                'register',
                compact('user', 'token')
            );
            
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        // on vérifie si le token est valide, n'a pas expiré et n'a pas été modifier
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            // Recupère le payload
            $payload = $jwt->getPayload($token);
            
            // Recupère le user
            $user = $userRepository->find($payload['user_id']);

            // On vérifie que l'utilisateur existe et n'a pas activé le compte
            if ($user && !$user->getisVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Le compte est activé');
                return $this->redirectToRoute('app_main');
            }

        }
        // gestions des erreurs
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
        // Fonction pour renvoyer la verif
    #[Route('/renvoiverif', name: 'app_resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {
        // Verification de connexion de l'utilisateur
        $user = $this->getUser();
        if (!$user){
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à la page');
            return $this->redirectToRoute('app_login');
        }
        // Verifié si l'utilisateur est déjà verifié
        if($user->getIsVerified()){
            $this->addFlash('warning', 'Vous êtes déjà activé');
            return $this->redirectToRoute('app_main');
        }
        // Generation du JWT 
        // Création du header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Création du payload
        $payload = [
            'user_id' => $user->getId()
        ];

        // Generation du token
        $token = $jwt->generate($header,$payload,$this->getParameter('app.jwtsecret'));

        //on envoi un mail
        $mail->send(
            'no-replay@zerveza.net',
            $user->getEmail(),
            'Activation de votre compte Zerveza',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'E-mail de vérification Re-envoyer');
        return $this->redirectToRoute('app_main');
    }
}
