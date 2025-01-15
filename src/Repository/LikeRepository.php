<?php

namespace App\Repository;

use App\Entity\Like;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function isMatch(User $user1, User $user2): bool
    {
        $likes = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.user_liker = :user1 AND l.user_liked = :user2')
            ->orWhere('l.user_liker = :user2 AND l.user_liked = :user1')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getSingleScalarResult();

        return $likes == 2;
    }
}
