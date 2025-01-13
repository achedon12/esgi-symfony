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
                $discussion->setApproved(true);
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

    #[Route('/forceDiscussion', name: 'forceDiscussion')]
    public function forceDiscussion(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $slidedUserId = $data['slidedUserId'] ?? null;
        $slidedUser = $entityManager->getRepository(User::class)->find($slidedUserId);

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }

        $like = new Like();
        $like->setUserLiker($user);
        $like->setUserLiked($slidedUser);
        $like->setCreationDate(new DateTimeImmutable());
        $entityManager->persist($like);
        $entityManager->flush();
        $slidedUser->setScore($slidedUser->getScore() + 1);

        $discussion = new Discussion();
        $discussion->setUserOne($user);
        $discussion->setUserTwo($slidedUser);
        $discussion->setCreationDate(new DateTimeImmutable());
        $discussion->setApproved(false);
        $entityManager->persist($discussion);
        $entityManager->flush();

        return $this->json(['status' => 'success', 'discussionId' => $discussion->getId()]);
    }

    #[Route('/suggestion', name: 'suggestion')]
    public function suggestion(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found or not an instance of App\Entity\User');
        }
        $suggestedUser = $this->userRepository->findSuggestedUsers($user);
        $distance = $this->calculateDistance($user->getLatitude(), $user->getLongitude(), $suggestedUser->getLatitude(), $suggestedUser->getLongitude());

        return $this->render('home/suggestion/suggestion.html.twig', [
            'suggestedUser' => $suggestedUser,
            'distance' => $distance,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float|int
    {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
