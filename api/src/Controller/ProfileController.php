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
 * Class: ProfileController
 * Description: Manages user profile and their "pack" (animals).
 * Responsibilities:
 * - Displays the user's "pack" (list of animals).
 * - Handles profile editing.
 * - Manages adding and editing animal profiles.
 * -------------------------------------------------------------
 */

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\User;
use App\Form\AnimalType;
use App\Form\UserProfileType;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/my-pack')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'app_my_pack')]
    public function index(\App\Repository\PostRepository $postRepository, \App\Repository\UserRepository $userRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('profile/my_pack.html.twig', [
            'profileUser' => $user,
            'packMembers' => $this->getPackMembers($user, $userRepository),
            'posts' => $postRepository->findProfilePosts($user),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_edit_profile', defaults: ['id' => null])]
    public function editProfile(?User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // If no ID, target current user
        $targetUser = $user ?? $currentUser;

        // Security: Can only edit self or managed account
        if ($targetUser !== $currentUser && !$targetUser->isManagedBy($currentUser)) {
            throw $this->createAccessDeniedException('You do not have permission to edit this profile.');
        }

        $form = $this->createForm(UserProfileType::class, $targetUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Update session locale for immediate effect
            $request->getSession()->set('_locale', $targetUser->getLocale());

            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_user_profile', ['id' => $targetUser->getId()]);
        }

        return $this->render('profile/edit_profile.html.twig', [
            'form' => $form->createView(),
            'targetUser' => $targetUser
        ], new Response(null, $form->isSubmitted() ? 422 : 200));
    }

    #[Route('/add-animal', name: 'app_add_animal')]
    public function addAnimal(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $petUser = new User();
            $petUser->setFirstName($animal->getName());
            $petUser->setEmail('pet_' . uniqid() . '@pawsocial.internal');
            $petUser->setRoles(['ROLE_PET']);
            $petUser->setAccountType('pet');

            /** @var User $currentUser */
            $currentUser = $this->getUser();

            // Use the bi-directional adder to keep collections in sync
            $currentUser->addManagedAccount($petUser);

            $petUser->setPassword(
                $userPasswordHasher->hashPassword(
                    $petUser,
                    'pet_password_' . uniqid()
                )
            );

            $animal->setUserAccount($petUser);

            $entityManager->persist($petUser);
            $entityManager->persist($animal);
            $entityManager->flush();

            $this->addFlash('success', 'New member added to your pack!');

            return $this->redirectToRoute('app_my_pack');
        }

        return $this->render('profile/animal_form.html.twig', [
            'form' => $form,
            'title' => 'Add new Pet'
        ]);
    }

    #[Route('/animal/{id}/edit', name: 'app_edit_animal')]
    public function editAnimal(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        // Security check: ManagedBy check
        $petUser = $animal->getUserAccount();
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$petUser || $petUser->getManagedBy() !== $currentUser) {
            if ($currentUser !== $petUser) {
                throw $this->createAccessDeniedException();
            }
        }

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update User name to match Animal name if changed
            $petUser->setFirstName($animal->getName());

            $entityManager->flush();
            $this->addFlash('success', 'Pet profile updated!');

            return $this->redirectToRoute('app_my_pack');
        }

        return $this->render('profile/animal_form.html.twig', [
            'form' => $form,
            'title' => 'Edit ' . $animal->getName()
        ]);
    }

    #[Route('/profile/{id}', name: 'app_user_profile')]
    public function show(User $user, \App\Repository\PostRepository $postRepository, \App\Repository\UserRepository $userRepository): Response
    {
        return $this->render('profile/my_pack.html.twig', [
            'profileUser' => $user,
            'packMembers' => $this->getPackMembers($user, $userRepository),
            'posts' => $postRepository->findProfilePosts($user),
        ]);
    }

    /**
     * Helper to get family/pack members based on account type.
     * 
     * @param User $user
     * @param \App\Repository\UserRepository $userRepository
     * @return User[]
     */
    private function getPackMembers(User $user, \App\Repository\UserRepository $userRepository): array
    {
        if ($user->getAccountType() === 'pet' && $user->getManagedBy()) {
            $owner = $user->getManagedBy();
            $members = [$owner];
            // Fetch siblings directly from repository to be sure
            $siblings = $userRepository->findBy(['managedBy' => $owner]);
            foreach ($siblings as $sibling) {
                if ($sibling->getId() !== $user->getId()) {
                    $members[] = $sibling;
                }
            }
            return $members;
        }

        // Fetch managed accounts directly from repository
        return $userRepository->findBy(['managedBy' => $user]);
    }
}
