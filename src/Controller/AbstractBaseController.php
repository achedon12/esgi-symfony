<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DiscussionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractBaseController extends AbstractController
{
    protected DiscussionRepository $discussionRepository;

    public function __construct(DiscussionRepository $discussionRepository)
    {
        $this->discussionRepository = $discussionRepository;
    }

    protected function initializeUser(): array
    {
        $user = $this->getUser();
        $this->ensureUserIsValid($user);

        return [$user];
    }

    protected function initializeUserAndDescription(): array
    {
        $user = $this->getUser();
        $this->ensureUserIsValid($user);
        $discussions = $this->getDiscussionsForUser($user);

        return [$user, $discussions];
    }

    private function ensureUserIsValid($user): void
    {
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
    }

    private function getDiscussionsForUser(User $user): array
    {
        $discussions = $this->discussionRepository->findByUser($user);

        array_map(function ($discussion) use ($user) {
            if ($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $discussions;
    }
}