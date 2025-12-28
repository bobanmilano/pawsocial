<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:make-admin',
    description: 'Promotes a user to ROLE_ADMIN_USER',
)]
class MakeAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user to promote')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('User with email "%s" not found.', $email));
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN_USER', $roles)) {
            $roles[] = 'ROLE_ADMIN_USER';
            // Also add standard ROLE_ADMIN for Symfony compatibility just in case
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles(array_unique($roles));
            $this->entityManager->flush();
            $io->success(sprintf('User "%s" has been promoted to admin!', $email));
        } else {
            $io->note(sprintf('User "%s" is already an admin.', $email));
        }

        return Command::SUCCESS;
    }
}
