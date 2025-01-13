<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DiscussionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractController
{

    public function __construct(private readonly DiscussionRepository $discussionRepository)
    {
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function index(Request $request, User $target): Response
    {
        $user = $this->getUser();

        $discussions = $this->discussionRepository->findByUser($user);

        array_map(function($discussion) use ($user) {
            if($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);

        return $this->render('user/index.html.twig', [
            'target' => $target,
            'user' => $user,
            'redirect_url' => $request->query->get('redirect_url') ?? 'app_home_index',
            'discussions' => $discussions
        ]);
    }
}
