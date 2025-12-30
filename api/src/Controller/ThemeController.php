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

    #[Route('/save-custom-colors', name: 'app_save_custom_colors', methods: ['POST'])]
    public function saveCustomColors(Request $request, \Doctrine\ORM\EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            return $this->redirectToRoute('app_login');
        }

        $primaryColor = $request->request->get('primary_color');
        $secondaryColor = $request->request->get('secondary_color');

        $user->setPrimaryColor($primaryColor);
        $user->setSecondaryColor($secondaryColor);

        $entityManager->flush();

        // Automatically switch to standard theme mode but with user overrides valid
        // Actually, let's set a specific session flag to indicate "User Custom" is active
        $request->getSession()->set('active_theme', 'custom');

        $this->addFlash('success', 'Custom colors saved! 🎨');

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_edit_profile'));
    }
}
