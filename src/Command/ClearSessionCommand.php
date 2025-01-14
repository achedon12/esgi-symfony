<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:clear-session',
    description: 'Clears the PHP session daily.',
)]
class ClearSessionCommand extends Command
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        parent::__construct();
        $this->requestStack = $requestStack;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $session = $this->requestStack->getSession();
        $session->clear();

        $output->writeln('Session cleared successfully.');

        return Command::SUCCESS;
    }
}