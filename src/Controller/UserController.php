<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\DiscussionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractBaseController
{
    public function __construct(DiscussionRepository $discussionRepository)
    {
        parent::__construct($discussionRepository);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function index(Request $request, User $target): Response
    {
        [$user, $discussions] = $this->initializeUserAndDescription();

        return $this->render('user/index.html.twig', [
            'target' => $target,
            'user' => $user,
            'redirect_url' => $request->query->get('redirect_url') ?? 'app_home_index',
            'discussions' => $discussions
        ]);
    }
}