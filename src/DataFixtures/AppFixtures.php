<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\Gender;
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
                ->setPassword($this->hasher->hashPassword($regularUser, 'regular'))
                ->setCreationDate(new \DateTimeImmutable($now = $faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setLongitude($faker->longitude())
                ->setLatitude($faker->latitude())
                ->setImage("https://thispersondoesnotexist.com/")
                ->setRoles(['ROLE_USER'])
                ->setBio($faker->sentence(10))
                ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')))
                ->setGender($faker->randomElement(Gender::cases()))
                ->setInterests($faker->words($faker->numberBetween(1, 5)))
                ->setScore($faker->numberBetween(0, 100));
            if($faker->boolean(90)) {
                $regularUser->setUpdateDate(new \DateTimeImmutable($faker->dateTimeBetween($now, 'now')->format('Y-m-d H:i:s')));
            }
            $manager->persist($regularUser);
        }

        // create admin user
        $adminUser = new User();
        $adminUser
            ->setEmail($faker->email())
            ->setPassword($this->hasher->hashPassword($adminUser, 'admin'))
            ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setLongitude($faker->longitude())
            ->setLatitude($faker->latitude())
            ->setImage("https://thispersondoesnotexist.com/")
            ->setRoles(['ROLE_ADMIN'])
            ->setBio($faker->sentence(10))
            ->setScore($faker->numberBetween(0, 100))
            ->setInterests($faker->words($faker->numberBetween(1, 5)))
            ->setGender($faker->randomElement(Gender::cases()))
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')));
        $manager->persist($adminUser);
    }
}
