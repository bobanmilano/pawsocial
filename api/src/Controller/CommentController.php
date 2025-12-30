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
        $submittedToken = $request->request->get('_token');

        if ($this->getParameter('app.csrf_protection_enabled') && !$this->isCsrfTokenValid('comment_add' . $post->getId(), $submittedToken)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if ($content) {
            $comment = new Comment();
            $comment->setContent($content);
            /** @var \App\Entity\User|null $user */
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setPost($post);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment added!');
        } else {
            $this->addFlash('danger', 'Comment cannot be empty.');
        }

        // Turbo Stream Response
        // We check loosely for the header because getPreferredFormat() can be finicky with q-values
        $acceptHeader = $request->headers->get('Accept', '');
        if (str_contains($acceptHeader, 'text/vnd.turbo-stream.html')) {
            $response = $this->render('feed/comment_stream.stream.html.twig', [
                'post' => $post,
            ]);
            $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
            return $response;
        }

        return $this->redirectToRoute('app_feed');
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $em, Request $request): Response
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

        if ($this->getParameter('app.csrf_protection_enabled') && !$this->isCsrfTokenValid('comment_delete' . $comment->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', 'Comment deleted.');

        // Turbo Stream Response
        $acceptHeader = $request->headers->get('Accept', '');
        if (str_contains($acceptHeader, 'text/vnd.turbo-stream.html')) {
            $response = $this->render('feed/comment_stream.stream.html.twig', [
                'post' => $comment->getPost(),
            ]);
            $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
            return $response;
        }

        return $this->redirectToRoute('app_feed');
    }
}
