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
    public function like(Post $post, PostLikeRepository $likeRepo, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

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

        // Return the new count
        $count = $likeRepo->count(['post' => $post]);

        return $this->json([
            'isLiked' => $isLiked,
            'count' => $count,
        ]);
    }
}
