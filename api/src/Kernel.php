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
 * Class: Kernel
 * Description: The kernel of the Symfony application.
 * Responsibilities:
 * - Bootstraps the application.
 * - Manages the service container and bundle loading.
 * -------------------------------------------------------------
 */



namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;


class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}