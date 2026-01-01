<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ThemeController extends AbstractController
{
    #[Route('/switch-theme/{theme}', name: 'app_switch_theme')]
    public function switchTheme(string $theme, Request $request): Response
    {
        $validThemes = ['soft-paw', 'sunny-park', 'modern-clay', 'dark-mode'];

        if (in_array($theme, $validThemes)) {
            $request->getSession()->set('active_theme', $theme);
            $this->addFlash('success', 'Theme updated: ' . ucfirst(str_replace('-', ' ', $theme)));
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_edit_profile'));
    }

    #[Route('/save-custom-colors/{id}', name: 'app_save_custom_colors', defaults: ['id' => null], methods: ['POST'])]
    public function saveCustomColors(?\App\Entity\User $targetUser, Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }
        // $currentUser is now known to be non-null and is @var User

        $userToUpdate = $targetUser ?? $currentUser;

        // Security check
        if ($userToUpdate !== $currentUser && !$userToUpdate->isManagedBy($currentUser)) {
            throw $this->createAccessDeniedException();
        }

        $primaryColor = $request->request->get('primary_color');
        $secondaryColor = $request->request->get('secondary_color');

        $userToUpdate->setPrimaryColor($primaryColor);
        $userToUpdate->setSecondaryColor($secondaryColor);

        $entityManager->flush();

        // Automatically switch to standard theme mode but with user overrides valid
        $request->getSession()->set('active_theme', 'custom');

        $this->addFlash('success', 'Custom colors saved for ' . $userToUpdate->getFirstName() . '! 🎨');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_edit_profile', ['id' => $userToUpdate->getId()]));
    }
}
