<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Context\SecurityContextInterface; // Deprecated but concept remains
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route('/auth/switch')]
#[IsGranted('ROLE_USER')]
class SwitchUserController extends AbstractController
{
    #[Route('/{id}', name: 'app_switch_user')]
    public function switch(User $targetUser, Request $request, Security $security, EventDispatcherInterface $eventDispatcher): Response
    {
        $currentUser = $this->getUser();
        //$session = $request->getSession();

        // Check if we are switching TO a pet
        if ($targetUser->getManagedBy() === $currentUser) {
            // Switching User -> Pet
            // Store original user ID in session to switch back
            $request->getSession()->set('_impersonating_user_id', $currentUser->getId());

            return $this->authenticateUser($targetUser, $request, $eventDispatcher);
        }

        // Check if we are switching BACK (Pet -> Owner)
        // Actually, if we are logged in as Pet, we might want to just click "Switch Back" which calls this with owner ID?
        // Or we have a dedicated "switch back" route.
        // Let's support explicit switch if authorized.

        // If logged in as Pet, allow switching to Owner (ManagedBy)
        if ($currentUser instanceof User && $currentUser->getManagedBy() === $targetUser) {
            // Switching Pet -> Owner
            $request->getSession()->remove('_impersonating_user_id');
            return $this->authenticateUser($targetUser, $request, $eventDispatcher);
        }

        // Verify session for extra security if switching back
        $originalUserId = $request->getSession()->get('_impersonating_user_id');
        if ($originalUserId && (int) $originalUserId === $targetUser->getId()) {
            $request->getSession()->remove('_impersonating_user_id');
            return $this->authenticateUser($targetUser, $request, $eventDispatcher);
        }

        throw $this->createAccessDeniedException('You are not allowed to switch to this user.');
    }

    private function authenticateUser(User $user, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        // Manually log in the user
        // We use 'main' firewall usually
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        // Fire the login event manually (optional but good for listeners)
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event, 'security.interactive_login');

        $this->addFlash('success', 'Switched identity to ' . ($user->getFirstName() ?? $user->getEmail()));

        return $this->redirectToRoute('app_feed');
    }
}
