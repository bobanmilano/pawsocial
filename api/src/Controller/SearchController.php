<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $query = $request->query->get('q');
        $results = [];

        if ($query) {
            // Search for users by name or email, or animals by name/breed
            $results = $userRepository->searchUsersAndPets($query);
        }

        return $this->render('main/search.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}
