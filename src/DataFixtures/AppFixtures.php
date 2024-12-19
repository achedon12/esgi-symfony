<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private const NUMBER_OF_USERS = 50;
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $this->loadUsers($manager, $faker);
        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager, $faker): void
    {
        // create regular users
        for($i = 0; $i < self::NUMBER_OF_USERS; $i++) {
            $regularUser = new User();
            $regularUser
                ->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($regularUser, 'regular'));
            $manager->persist($regularUser);
        }

        // create admin user
        $adminUser = new User();
        $adminUser
            ->setEmail($faker->email())
            ->setPassword($this->hasher->hashPassword($adminUser, 'admin'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);
    }
}
