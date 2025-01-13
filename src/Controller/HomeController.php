<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Like;
use App\Entity\User;
use App\Repository\DiscussionRepository;
use App\Repository\LikeRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home', name: 'app_home_')]

class HomeController extends AbstractController
{

    public function __construct(private readonly UserRepository $userRepository,
                                private readonly DiscussionRepository $discussionRepository,
                                private readonly EntityManagerInterface $entityManager,
                                private readonly LikeRepository $likeRepository)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
        $users = $this->userRepository->findAppropriatedUsers($user);
        $discussions = $this->discussionRepository->findByUser($user);

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

    #[Route('/refresh', name: 'refresh')]
    public function refresh(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
        $users = $this->userRepository->findAppropriatedUsers($user);
        $discussions = $this->discussionRepository->findByUser($user);

        return $this->render('home/index.html.twig', [
            'users' => $users,
            'user' => $user,
            'discussions' => $discussions
        ]);
    }

    #[Route('/slide', name: 'slide')]
    public function slide(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $direction = $data['direction'] ?? null;
        $slidedUserId = $data['slidedUserId'] ?? null;
        $slidedUser = $this->entityManager->getRepository(User::class)->find($slidedUserId);

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
        if ($direction === 'like') {
            $like = new Like();
            $like->setUserLiker($user);
            $like->setUserLiked($slidedUser);
            $like->setCreationDate(new DateTimeImmutable());
            $this->entityManager->persist($like);
            $this->entityManager->flush();
            $slidedUser->setScore($slidedUser->getScore() + 1);

            if($this->likeRepository->isMatch($user, $slidedUser)) {
                $discussion = new Discussion();
                $discussion->setUserOne($user);
                $discussion->setUserTwo($slidedUser);
                $discussion->setCreationDate(new DateTimeImmutable());
                $this->entityManager->persist($discussion);
                $this->entityManager->flush();
                return $this->json(['status' => 'match', 'discussionId' => $discussion->getId()]);
            }

            return $this->json(['status' => 'success', 'message' => 'User liked successfully']);
        } elseif ($direction === 'dislike') {
            $slidedUser->setScore($slidedUser->getScore() - 1);
            $this->entityManager->flush();
            return $this->json(['status' => 'success', 'message' => 'User disliked successfully']);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Direction not found'], 400);
        }
    }

    #[Route('/suggestion', name: 'suggestion')]
    public function suggestion(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
        $suggestedUser = $this->userRepository->findSuggestedUsers($user);

        return $this->render('home/suggestion/suggestion.html.twig', [
            'suggestedUser' => $suggestedUser,
            'user' => $user
        ]);
    }
}
