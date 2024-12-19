<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Message;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/discussion', name: 'app_discussion_')]

class DiscussionController extends AbstractController
{

    #[Route('/{id}', name: 'index', requirements: ['id' => '\d+'])]
    public function index(string $id, DiscussionRepository $discussionRepository): Response
    {
        $user = $this->getUser();
        $discussion = $discussionRepository->find($id);

        if (!$discussion) {
            throw $this->createNotFoundException('Discussion not found.');
        }

        $discussions = $discussionRepository->findByUser($user);

        return $this->render('discussion/index.html.twig', [
            'user' => $user,
            'discussion' => $discussion,
            'discussions' => $discussions
        ]);
    }

    #[Route('/send', name: 'send', methods: ['POST'])]
    public function send(Request $request, DiscussionRepository $discussionRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $content = $request->request->get('message');
        $discussionId = $request->request->get('discussion_id');

        if (empty($content) || empty($discussionId)) {
            $this->addFlash('error', 'Le message ou la discussion est invalide.');
            return $this->redirectToRoute('app_home_index');
        }

        $discussion = $discussionRepository->find($discussionId);
        if (!$discussion) {
            throw $this->createNotFoundException('Discussion introuvable.');
        }

        $message = new Message();
        $message->setAuthor($user)
            ->setContent($content)
            ->setDiscussion($discussion)
            ->setCreationDate(new \DateTimeImmutable());

        $discussion->setUpdateDate(new \DateTimeImmutable());

        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_discussion_index', ['id' => $discussionId]);
    }

}
