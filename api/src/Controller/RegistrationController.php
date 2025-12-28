<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 *
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers,
 * animal shelters, and organizations to connect, share, and collaborate.
 *
 * This software is proprietary and confidential. Any use, reproduction, or
 * distribution without explicit written permission from the author is strictly prohibited.
 *
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 *
 * Class: RegistrationController
 * Description: Handles new user registration.
 * Responsibilities:
 * - Processes user registration forms.
 * - Hashes user passwords securely.
 * - Persists new user data to the database.
 * - Redirects users after successful registration.
 * -------------------------------------------------------------
 */



namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface
        $entityManager
    ): Response {
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

            // Set default profile type if not set (handled by default value in entity, but good to be explicit if needed)

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login'); // Redirect to login after registration
            // Alternatively, auto-login:
            // return $userAuthenticator->authenticateUser($user, $authenticator, $request);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}