<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-inactive-users',
    description: 'Clean up inactive users',
)]
class CleanInactiveUsersCommand extends Command
{
    protected static $defaultName = 'app:clean-inactive-users';
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Clean up inactive users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inactivityPeriod = new \DateTime('-1 year');

        $inactiveUsers = $this->userRepository->findInactiveUsers($inactivityPeriod);

        foreach ($inactiveUsers as $user) {
            $this->entityManager->remove($user);
        }

        $this->entityManager->flush();

        $io->success('Inactive users have been cleaned up.');

        return Command::SUCCESS;
    }
}
