<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class LikeController extends AbstractController
{
    #[Route('/post/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(Post $post, PostLikeRepository $likeRepo, EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();

        if ($this->getParameter('app.csrf_protection_enabled') && !$this->isCsrfTokenValid('like' . $post->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // check if already liked
        $existingLike = $likeRepo->findOneBy(['post' => $post, 'user' => $user]);

        if ($existingLike) {
            $em->remove($existingLike);
            $em->flush();
            $isLiked = false;
        } else {
            $like = new PostLike();
            $like->setPost($post);
            $like->setUser($user);
            $em->persist($like);
            $em->flush();
            $isLiked = true;
        }

        $em->refresh($post);

        // Return the new count
        // For Turbo: Render only the updated post card (containing the frame) to avoid reloading the whole feed.
        return $this->render('feed/_post_card.html.twig', [
            'post' => $post,
        ]);
    }
}
