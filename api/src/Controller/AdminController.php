<?php

namespace App\Controller;

use App\Repository\AdminMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN_USER')]
class AdminController extends AbstractController
{
    #[Route('/messages', name: 'app_admin_messages')]
    public function messages(AdminMessageRepository $messageRepository): Response
    {
        // Simple list of all messages, newest first
        $messages = $messageRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/messages.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/users', name: 'app_admin_users')]
    public function users(\App\Repository\UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/{id}/role/{role}', name: 'app_admin_change_role', methods: ['POST'])]
    public function changeRole(int $id, string $role, \App\Repository\UserRepository $userRepository, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        // Define allowed roles map
        $allowedRoles = [
            'base' => 'ROLE_BASE_USER',
            'commercial' => 'ROLE_COMMERCIAL_USER',
            'gold' => 'ROLE_GOLD_USER',
            'premium' => 'ROLE_PREMIUM_USER',
            'admin' => 'ROLE_ADMIN_USER',
        ];

        if (!array_key_exists($role, $allowedRoles)) {
            $this->addFlash('danger', 'Invalid role selected.');
            return $this->redirectToRoute('app_admin_users');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $newRole = $allowedRoles[$role];
        $currentRoles = $user->getRoles();

        // Remove old custom roles to avoid stacking mutually exclusive tiers (simplification)
        $rolesToRemove = array_values($allowedRoles);
        $cleanRoles = array_diff($currentRoles, $rolesToRemove);

        // Add new role
        $cleanRoles[] = $newRole;
        // Ensure Admin gets standard ROLE_ADMIN too
        if ($newRole === 'ROLE_ADMIN_USER') {
            $cleanRoles[] = 'ROLE_ADMIN';
        }

        $user->setRoles(array_unique($cleanRoles));
        $entityManager->flush();

        $this->addFlash('success', sprintf('User %s updated to %s', $user->getEmail(), strtoupper($role)));

        return $this->redirectToRoute('app_admin_users');
    }
}
