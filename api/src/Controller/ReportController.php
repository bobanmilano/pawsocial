<?php

namespace App\Controller;

use App\Entity\AdminMessage;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReportController extends AbstractController
{
    #[Route('/post/{id}/report', name: 'app_report_post')]
    public function reportPost(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $reason = $request->request->get('reason');

            if (trim($reason)) {
                $user = $this->getUser();
                if (!$user instanceof \App\Entity\User) {
                    throw $this->createAccessDeniedException();
                }

                $message = new AdminMessage();
                $message->setSender($user);
                $message->setRelatedPost($post);
                $message->setMessage("REPORTED POST [ID: {$post->getId()}]: " . $reason);

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Report sent to admin. Thank you for keeping the pack safe! 🐾');
                return $this->redirectToRoute('app_feed');
            } else {
                $this->addFlash('danger', 'Please provide a reason for the report.');
            }
        }

        return $this->render('report/post_report.html.twig', [
            'post' => $post
        ]);
    }
}
