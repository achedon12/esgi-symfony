<?php

namespace App\Controller;

use App\Repository\DiscussionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home', name: 'app_home_')]

class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UserRepository $userRepository, DiscussionRepository $discussionRepository): Response
    {
        $users = $userRepository->findBy([], null, 5);
        $user = $this->getUser();
        $discussions = $discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $this->render('home/index.html.twig', [
            'users' => $users,
            'user' => $user,
            'discussions' => $discussions
        ]);
    }
}
