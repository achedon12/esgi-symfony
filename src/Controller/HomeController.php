<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Like;
use App\Entity\User;
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

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository         $userRepository,
        private readonly LikeRepository         $likeRepository,
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $users = $this->userRepository->findAppropriatedUsers($this->getUser());

        return $this->render('home/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/slide', name: 'slide')]
    public function slide(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $direction = $data['direction'] ?? null;
        $slidedUserId = $data['slidedUserId'] ?? null;
        $slidedUser = $this->entityManager->getRepository(User::class)->find($slidedUserId);

        if ($direction === 'like') {
            return $this->handleLike($this->getUser(), $slidedUser);
        } elseif ($direction === 'dislike') {
            return $this->handleDislike($slidedUser);
        } else {
            return $this->json(['status' => 'error', 'message' => 'Direction not found'], 400);
        }
    }

    #[Route('/forceDiscussion', name: 'forceDiscussion')]
    public function forceDiscussion(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $slidedUserId = $data['slidedUserId'] ?? null;
        $slidedUser = $this->entityManager->getRepository(User::class)->find($slidedUserId);

        $this->createLike($this->getUser(), $slidedUser);
        $discussion = $this->createDiscussion($this->getUser(), $slidedUser, false);

        return $this->json(['status' => 'success', 'discussionId' => $discussion->getId()]);
    }

    #[Route('/suggestion', name: 'suggestion')]
    public function suggestion(): Response
    {
        $suggestedUser = $this->userRepository->findSuggestedUsers($this->getUser());
        $distance = $this->calculateDistance($this->getUser()->getLatitude(), $this->getUser()->getLongitude(), $suggestedUser->getLatitude(), $suggestedUser->getLongitude());

        return $this->render('home/suggestion/suggestion.html.twig', [
            'suggestedUser' => $suggestedUser,
            'distance' => $distance,
        ]);
    }

    private function handleLike($user, $slidedUser): Response
    {
        $this->createLike($user, $slidedUser);
        $slidedUser->setScore($slidedUser->getScore() + 1);

        if ($this->likeRepository->isMatch($user, $slidedUser)) {
            $discussion = $this->createDiscussion($user, $slidedUser, true);
            return $this->json(['status' => 'match', 'discussionId' => $discussion->getId()]);
        }

        return $this->json(['status' => 'success', 'message' => 'User liked successfully']);
    }

    private function handleDislike($slidedUser): Response
    {
        $slidedUser->setScore($slidedUser->getScore() - 1);
        $this->entityManager->flush();
        return $this->json(['status' => 'success', 'message' => 'User disliked successfully']);
    }

    private function createLike($user, $slidedUser): void
    {
        $like = new Like();
        $like->setUserLiker($user);
        $like->setUserLiked($slidedUser);
        $like->setCreationDate(new DateTimeImmutable());
        $this->entityManager->persist($like);
        $this->entityManager->flush();
    }

    private function createDiscussion($user, $slidedUser, bool $approved): Discussion
    {
        $discussion = new Discussion();
        $discussion->setUserOne($user);
        $discussion->setUserTwo($slidedUser);
        $discussion->setCreationDate(new DateTimeImmutable());
        $discussion->setApproved($approved);
        $this->entityManager->persist($discussion);
        $this->entityManager->flush();

        return $discussion;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float|int
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}