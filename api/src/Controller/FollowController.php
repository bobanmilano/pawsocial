<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FollowController extends AbstractController
{
    #[Route('/user/{id}/follow', name: 'app_user_follow', methods: ['POST'])]
    public function follow(User $userToFollow, EntityManagerInterface $em, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($this->getParameter('app.csrf_protection_enabled') && !$this->isCsrfTokenValid('follow' . $userToFollow->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($currentUser === $userToFollow) {
            return $this->json(['error' => 'You cannot follow yourself'], 400);
        }

        if (!$currentUser->isFollowing($userToFollow)) {
            $currentUser->follow($userToFollow);
            $em->flush();
        }

        // Turbo Update
        if ($request->getPreferredFormat() === 'turbo-stream' || str_contains($request->headers->get('Accept', ''), 'text/vnd.turbo-stream.html')) {
            $response = $this->render('follow/_button.stream.html.twig', [
                'user' => $userToFollow,
                'isFollowing' => true // We just followed
            ]);
            $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
            return $response;
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_main'));
    }

    #[Route('/user/{id}/unfollow', name: 'app_user_unfollow', methods: ['POST'])]
    public function unfollow(User $userToUnfollow, EntityManagerInterface $em, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($this->getParameter('app.csrf_protection_enabled') && !$this->isCsrfTokenValid('unfollow' . $userToUnfollow->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($currentUser->isFollowing($userToUnfollow)) {
            $currentUser->unfollow($userToUnfollow);
            $em->flush();
        }

        // Turbo Update
        if ($request->getPreferredFormat() === 'turbo-stream' || str_contains($request->headers->get('Accept', ''), 'text/vnd.turbo-stream.html')) {
            $response = $this->render('follow/_button.stream.html.twig', [
                'user' => $userToUnfollow,
                'isFollowing' => false // We just unfollowed
            ]);
            $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
            return $response;
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_main'));
    }
}
