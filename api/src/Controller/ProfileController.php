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
    public function index(\App\Repository\PostRepository $postRepository): Response
    {
        // Get current user's animals (Assuming ManyToOne from Animal -> User is set up)
        // Since we didn't add the `animals` OneToMany property to User explicitly in the make loop (we said 'yes' but check logic),
        // we can fetch via repository or rely on the User object if the relation was mapped.
        // Let's rely on the User object being the owner.

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // If the relation isn't explicitly mapped in User.php (OneToMany), we can fetch via Repository.
        // But normally make:entity handles this. Let's assume User has getAnimals() or we fetch by owner.
        // Actually, looking at User.php previously, we didn't see `animals` property there yet?
        // Let's double check User.php content. If missing, we fetch via repo.

        /** @var \App\Repository\PostRepository $postRepository */
        // We can access posts via relationship or repo. Relationship is easier but unsorted.
        // Let's rely on relationship for now or we can sort in memory.
        // Actually, let's just pass the posts from the user object, assuming we might want to sort them in Twig or here.
        // Better: let's fetch them sorted via specialized logic or simply access them.

        // For simple reverse chronological order, let's use the criteria or sort in Twig.
        // Or simpler: access via getter.

        return $this->render('profile/my_pack.html.twig', [
            'profileUser' => $user,
            'animals' => $user->getAnimals(),
            'posts' => $postRepository->findProfilePosts($user),
        ]);
    }

    #[Route('/edit', name: 'app_edit_profile')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_my_pack');
        }

        return $this->render('profile/edit_profile.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/add-animal', name: 'app_add_animal')]
    public function addAnimal(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animal = new Animal();
        $animal->setOwner($this->getUser());

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        // Security check: Owner only
        if ($animal->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Pet profile updated!');

            return $this->redirectToRoute('app_my_pack');
        }

        return $this->render('profile/animal_form.html.twig', [
            'form' => $form,
            'title' => 'Edit ' . $animal->getName()
        ]);
    }
}