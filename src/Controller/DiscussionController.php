<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Message;
use App\Repository\DiscussionRepository;
use App\Repository\LikeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/discussion', name: 'app_discussion_')]
class DiscussionController extends AbstractController
{
    private DiscussionRepository $discussionRepository;
    private LikeRepository $likeRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        DiscussionRepository   $discussionRepository,
        LikeRepository         $likeRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->discussionRepository = $discussionRepository;
        $this->likeRepository = $likeRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/{id}', name: 'index', requirements: ['id' => '\d+'])]
    public function index(string $id): Response
    {
        $user = $this->getUser();
        $discussion = $this->discussionRepository->find($id);

        if (!$discussion) {
            throw $this->createNotFoundException('Discussion not found.');
        }

        if (!$this->isUserPartOfDiscussion($user, $discussion)) {
            throw $this->createAccessDeniedException('You are not allowed to access this discussion.');
        }

        $discussions = $this->discussionRepository->findByUser($user);
        $this->setDiscussionUser($discussions, $user);

        $isCurrentUserDemanding = $this->isCurrentUserDemanding($user, $discussion);

        return $this->render('discussion/index.html.twig', [
            'user' => $user,
            'discussion' => $discussion,
            'discussions' => $discussions,
            'isCurrentUserDemanding' => $isCurrentUserDemanding
        ]);
    }

    #[Route('/send', name: 'send', methods: ['POST'])]
    public function send(Request $request): Response
    {
        $user = $this->getUser();
        $content = $request->request->get('message');
        $discussionId = $request->request->get('discussion_id');

        if (empty($content) || empty($discussionId)) {
            $this->addFlash('error', 'Le message ou la discussion est invalide.');
            return $this->redirectToRoute('app_home_index');
        }

        $discussion = $this->discussionRepository->find($discussionId);
        if (!$discussion) {
            throw $this->createNotFoundException('Discussion introuvable.');
        }

        $message = new Message();
        $message->setAuthor($user)
            ->setContent($content)
            ->setDiscussion($discussion)
            ->setCreationDate(new DateTimeImmutable());

        $discussion->setUpdateDate(new DateTimeImmutable());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_discussion_index', ['id' => $discussionId]);
    }

    #[Route('/manage', name: 'manage', methods: ['POST'])]
    public function manage(Request $request): Response
    {
        $user = $this->getUser();
        $action = (int)$request->request->get('action');
        $discussionId = $request->request->get('discussion_id');

        $discussion = $this->discussionRepository->find($discussionId);
        if (!$discussion) {
            throw $this->createNotFoundException('Discussion introuvable.');
        }

        if ($action === 1) {
            $this->approveDiscussion($discussion, $user);
        } else {
            $this->entityManager->remove($discussion);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_home_index');
    }

    private function isUserPartOfDiscussion($user, $discussion): bool
    {
        return $discussion->getUserOne() === $user || $discussion->getUserTwo() === $user;
    }

    private function setDiscussionUser(array &$discussions, $user): void
    {
        array_map(function ($discussion) use ($user) {
            if ($discussion->getUserOne() === $user) {
                $discussion->setUserTwo($discussion->getUserTwo());
            } else {
                $discussion->setUserTwo($discussion->getUserOne());
            }
        }, $discussions);
    }

    private function isCurrentUserDemanding($user, $discussion): bool
    {
        $like = $this->likeRepository->findOneBy([
            'user_liker' => $user,
            'user_liked' => $discussion->getUserOne() === $user ? $discussion->getUserTwo() : $discussion->getUserOne()
        ]);

        return $like !== null;
    }

    private function approveDiscussion($discussion, $user): void
    {
        $discussion->setApproved(true);
        $user1 = $discussion->getUserOne();
        $user2 = $discussion->getUserTwo();
        $notMe = $user1->getId() === $user->getId() ? $user2 : $user1;

        $like = new Like();
        $like->setUserLiker($notMe);
        $like->setUserLiked($user);
        $like->setCreationDate(new DateTimeImmutable());

        $this->entityManager->persist($like);
        $this->entityManager->flush();

        $user->setScore($user->getScore() + 1);
    }
}