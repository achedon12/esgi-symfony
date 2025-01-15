<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCommand(
    name: 'app:clear-images',
    description: 'Clears the unused images.',
)]
class ClearImagesCommand extends Command
{
    private UserRepository $userRepository;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        parent::__construct();
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $imagesPath = 'public/uploads/profile_images/';

        $usedImages = $this->userRepository->findImages();

        $files = scandir($imagesPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (!in_array($file, $usedImages)) {
                unlink($imagesPath . $file);
            }
        }

        $output->writeln('Images cleared successfully.');
        return Command::SUCCESS;
    }
}