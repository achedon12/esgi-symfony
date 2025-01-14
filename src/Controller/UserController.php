<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function index(Request $request, User $target): Response
    {
        return $this->render('user/index.html.twig', [
            'target' => $target,
            'redirect_url' => $request->query->get('redirect_url') ?? 'app_home_index',
        ]);
    }
}