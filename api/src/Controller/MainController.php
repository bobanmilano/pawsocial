<?php

/**
 * -------------------------------------------------------------
 * Developed by Boban Milanovic BSc <boban.milanovic@gmail.com>
 *
 * Project: PawSocial Social Network
 * Description: A social network platform designed for pets, animal lovers,
 * animal shelters, and organizations to connect, share, and collaborate.
 *
 * This software is proprietary and confidential. Any use, reproduction, or
 * distribution without explicit written permission from the author is strictly prohibited.
 *
 * For licensing or collaboration inquiries, please contact:
 * Email: boban.milanovic@gmail.com
 * -------------------------------------------------------------
 *
 * Class: MainController
 * Description: Handles the main entry point of the application, typically the homepage.
 * Responsibilities:
 * - Renders the homepage view.
 * -------------------------------------------------------------
 */



namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }
}