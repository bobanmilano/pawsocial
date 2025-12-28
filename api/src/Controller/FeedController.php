<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FeedController extends AbstractController
{
    #[Route('/feed', name: 'app_feed')]
    public function index(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();
            $post->setAuthor($user);

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Your moment has been shared! 🐾');
            return $this->redirectToRoute('app_feed');
        }

        // Fetch "Feed" posts: All posts where showInFeed = true, ordered by newest
        // For MVP, we show ALL public posts. Later: Friends only.
        $feedPosts = $postRepository->findBy(['showInFeed' => true], ['createdAt' => 'DESC'], 50);

        return $this->render('feed/index.html.twig', [
            'form' => $form,
            'posts' => $feedPosts,
        ]);
    }

    #[Route('/feed/profile-only', name: 'app_feed_profile_only')]
    public function profileFeed(PostRepository $postRepository): Response
    {
        // Just a helper/test route to see what would be on the profile
        /** @var User $user */
        $user = $this->getUser();
        $posts = $postRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);

        return $this->render('feed/index.html.twig', [ // Reusing template for now or create specific one
            'posts' => $posts,
            'form' => null // No posting from here for now
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        // Check text/owner
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own posts.');
        }

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
            $this->addFlash('success', 'Post deleted.');
        }

        // Redirect back to where they came from (feed or profile)
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_feed'));
    }
}
