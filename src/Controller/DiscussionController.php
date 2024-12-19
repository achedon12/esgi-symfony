<?php

namespace App\Controller;

use App\Entity\Discussion;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/discussion', name: 'app_discussion_')]

class DiscussionController extends AbstractController
{

    #[Route('/{id}', name: 'index')]
    public function index(Discussion $discussion): Response
    {
        $user = $this->getUser();

        return $this->render('discussion/index.html.twig', [
            'user' => $user,
            'discussion' => $discussion
        ]);
    }
}
