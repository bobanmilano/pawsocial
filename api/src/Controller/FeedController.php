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
            /** @var User|null $user */
            $user = $this->getUser();
            $post->setAuthor($user);

            // Identity Check
            $session = $request->getSession();
            if ($session->get('active_identity_type') === 'animal' && $session->get('active_identity_id')) {
                // We should technically verify ownership again here for robustness
                // but checking session id against user's animals is decent.
                // For optimal perf, we'll just fetch reference if we trust session (which we do for owner check above).
                // Actually safer: find in user's collection.

                $activeAnimalId = $session->get('active_identity_id');
                $animals = $user->getAnimals();
                foreach ($animals as $animal) {
                    if ((string) $animal->getId() === (string) $activeAnimalId) {
                        $post->setPostedByAnimal($animal);
                        break;
                    }
                }
            }

            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash('success', 'Your moment has been shared! 🐾');

            // Turbo Stream Response for immediate update
            if ($request->getPreferredFormat() === 'turbo-stream' || str_contains($request->headers->get('Accept', ''), 'text/vnd.turbo-stream.html')) {
                // We need a fresh form
                $form = $this->createForm(\App\Form\PostType::class, new \App\Entity\Post());

                $response = $this->render('feed/post_created.stream.html.twig', [
                    'post' => $post,
                    'form' => $form->createView()
                ]);
                $response->headers->set('Content-Type', 'text/vnd.turbo-stream.html');
                return $response;
            }

            return $this->redirectToRoute('app_feed');
        }

        // Fetch "Feed" posts: All posts where showInFeed = true, ordered by newest
        // For MVP, we show ALL public posts. Later: Friends only.
        // Fetch "Feed" posts with eager loading to prevent N+1 queries
        $feedMode = $request->query->get('feed', 'all');
        /** @var User|null $user */
        $user = $this->getUser();
        $feedPosts = $postRepository->findSmartFeedPosts($user, $feedMode, 50);

        return $this->render('feed/index.html.twig', [
            'form' => $form,
            'posts' => $feedPosts,
            'feedMode' => $feedMode
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

    #[Route('/post/{id}', name: 'app_post_show', requirements: ['id' => '\d+'], priority: 2)]
    public function show(Post $post): Response
    {
        return $this->render('feed/show.html.twig', [
            'post' => $post,
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
