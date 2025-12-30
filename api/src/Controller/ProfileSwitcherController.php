<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileSwitcherController extends AbstractController
{
    #[Route('/switch-identity/{type}/{id}', name: 'app_switch_identity')]
    public function switch(string $type, string $id, Request $request): Response
    {
        // Security check: If switching to animal, must be owner
        /** @var User $user */
        $user = $this->getUser();
        $session = $request->getSession();

        if ($type === 'user') {
            $session->remove('active_identity_type');
            $session->remove('active_identity_id');
            $this->addFlash('success', 'Switched back to your profile.');
        } elseif ($type === 'animal') {
            // Verify ownership
            // We need to fetch the animal. Since we don't have AnimalRepository injected here yet,
            // we can iterate user's animals or inject Repo. To keep it simple and secure, iterate.

            $targetAnimal = null;
            foreach ($user->getAnimals() as $animal) {
                if ((string) $animal->getId() === $id) {
                    $targetAnimal = $animal;
                    break;
                }
            }

            if ($targetAnimal) {
                $session->set('active_identity_type', 'animal');
                $session->set('active_identity_id', $targetAnimal->getId());
                $session->set('active_identity_name', $targetAnimal->getName());
                $this->addFlash('success', 'You are now posting as ' . $targetAnimal->getName() . ' 🐾');
            } else {
                $this->addFlash('error', 'Invalid profile selection.');
            }
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_feed'));
    }
}
