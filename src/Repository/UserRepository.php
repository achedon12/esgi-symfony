<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAppropriatedUsers(User $user): array
    {
        $minScore = $user->getScore() - 20;
        $maxScore = $user->getScore() + 20;

        return $this->createQueryBuilder('u')
            ->andWhere('u.score BETWEEN :minScore AND :maxScore')
            ->setParameter('minScore', $minScore)
            ->setParameter('maxScore', $maxScore)
            ->andWhere('u.id != :id')
            ->setParameter('id', $user->getId())
            ->andWhere('u.gender = :sexualOrientation')
            ->setParameter('sexualOrientation', $user->getSexualOrientation())
            ->andWhere('u.id NOT IN (
            SELECT IDENTITY(l.user_liked) 
            FROM App\Entity\Like l 
            WHERE l.user_liker = :user
        )')
            ->setParameter('user', $user)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findSuggestedUsers(User $user): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->andWhere('u.id != :id')
            ->setParameter('id', $user->getId())
            ->andWhere('u.id NOT IN (
            SELECT IDENTITY(l.user_liked)
            FROM App\Entity\Like l
            WHERE l.user_liker = :user
        )')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        if (!empty($user->getInterests())) {
            $queryBuilder->orWhere('u.interests LIKE :interests')
                ->setParameter('interests', '%' . implode('%', $user->getInterests()) . '%');
        }

        if ($user->getBirthdate() !== null) {
            $birthdate = $user->getBirthdate();
            $startDate = (clone $birthdate)->modify('-10 days');
            $endDate = (clone $birthdate)->modify('+10 days');

            $queryBuilder->orWhere('u.birthdate BETWEEN :startDate AND :endDate')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        }

        if ($user->getLongitude() !== null && $user->getLatitude() !== null) {
            $longitude = $user->getLongitude();
            $latitude = $user->getLatitude();
            $range = 5.0;

            $queryBuilder->orWhere('u.longitude BETWEEN :minLongitude AND :maxLongitude')
                ->setParameter('minLongitude', $longitude - $range)
                ->setParameter('maxLongitude', $longitude + $range)
                ->orWhere('u.latitude BETWEEN :minLatitude AND :maxLatitude')
                ->setParameter('minLatitude', $latitude - $range)
                ->setParameter('maxLatitude', $latitude + $range);
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findInactiveUsers(\DateTime $inactiveSince): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.likes', 'l')
            ->andWhere('l.createdAt < :inactiveSince')
            ->setParameter('inactiveSince', $inactiveSince)
            ->getQuery()
            ->getResult();
    }

}
