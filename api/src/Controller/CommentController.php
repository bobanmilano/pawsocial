<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class CommentController extends AbstractController
{
    #[Route('/post/{id}/comment', name: 'app_post_comment', methods: ['POST'])]
    public function addComment(Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $content = trim($request->request->get('content'));

        if ($content) {
            $comment = new Comment();
            $comment->setContent($content);
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment added!');
        } else {
            $this->addFlash('danger', 'Comment cannot be empty.');
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $em): Response
    {
        // Allow deletion if user is author OR admin OR post owner
        $user = $this->getUser();
        $isAuthor = $comment->getAuthor() === $user;
        $isAdmin = $this->isGranted('ROLE_ADMIN_USER'); // Assuming we have this role hierarchy or check
        // Check post owner
        $isPostOwner = $comment->getPost()->getAuthor() === $user;

        if (!$isAuthor && !$isAdmin && !$isPostOwner) {
            throw $this->createAccessDeniedException('You cannot delete this comment.');
        }

        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Comment deleted.');

        return $this->redirectToRoute('app_feed');
    }
}
