<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $regularUser = new User();
        $regularUser
            ->setEmail('regular@email.com')
            ->setPassword($this->hasher->hashPassword($regularUser, 'regular'));
        $manager->persist($regularUser);

        $adminUser = new User();
        $adminUser
            ->setEmail('admin@email.com')
            ->setPassword($this->hasher->hashPassword($adminUser, 'admin'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);
    }
}
