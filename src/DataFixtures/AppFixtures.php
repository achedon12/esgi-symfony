<?php

namespace App\DataFixtures;

use App\Entity\Discussion;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\Gender;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private const NUMBER_OF_USERS = 50;
    private const NUMBER_OF_DISCUSSIONS = 10;
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $this->loadUsers($manager, $faker);
        // flush users (to later, create discussions)
        $manager->flush();

        // load more data for dev environment
        //if (getenv('APP_ENV') === 'dev') {
            $this->loadDiscussions($manager, $faker);
        //}
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
                ->setGender($faker->randomElement([User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_OTHER]))
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
            ->setEmail('admin@gmail.com')
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
            ->setGender($faker->randomElement([User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_OTHER]))
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')));
        $manager->persist($adminUser);

        // create a regular user with a specific email
        $specificUser = new User();
        $specificUser
            ->setEmail('regular@gmail.com')
            ->setPassword($this->hasher->hashPassword($specificUser, 'regular'))
            ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setLongitude($faker->longitude())
            ->setLatitude($faker->latitude())
            ->setImage("https://thispersondoesnotexist.com/")
            ->setRoles(['ROLE_USER'])
            ->setBio($faker->sentence(10))
            ->setScore($faker->numberBetween(0, 100))
            ->setInterests($faker->words($faker->numberBetween(1, 5)))
            ->setGender($faker->randomElement([User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_OTHER]))
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')));
        $manager->persist($specificUser);
    }

    private function loadDiscussions(ObjectManager $manager, $faker): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        /*for ($i = 0; $i < self::NUMBER_OF_DISCUSSIONS; $i++) {
            $userOne = $faker->randomElement($users);
            $userTwo = $faker->randomElement($users);

            // Ensure userOne is not the same as userTwo
            while ($userOne === $userTwo) {
                $userTwo = $faker->randomElement($users);
            }

            $discussion = new Discussion();
            $discussion
                ->setUserOne($userOne)
                ->setUserTwo($userTwo)
                ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')));
            $manager->persist($discussion);

            // Create messages for the discussion
            $numberOfMessages = $faker->numberBetween(5, 10);
            for ($j = 0; $j < $numberOfMessages; $j++) {
                $message = new Message();
                $message
                    ->setDiscussion($discussion)
                    ->setContent($faker->sentence())
                    ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
                    ->setAuthor($faker->randomElement([$userOne, $userTwo]));
                $manager->persist($message);

                // add messages to the discussion
                $discussion->addMessage($message);
                $manager->persist($discussion);
            }

            // add discussion to the users
            $manager->persist($userOne);
            $manager->persist($userTwo);
        }*/

        // create a discussion with a specific user
        $specificUser = $manager->getRepository(User::class)->findOneBy(['email' => 'regular@gmail.com']);
        $secondUser = $faker->randomElement($users);

        while ($specificUser === $secondUser) {
            $secondUser = $faker->randomElement($users);
        }

        $discussion = new Discussion();
        $discussion
            ->setUserOne($specificUser)
            ->setUserTwo($secondUser)
            ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')));

        $manager->persist($discussion);

        // Create messages for the discussion
        $numberOfMessages = $faker->numberBetween(5, 10);

        for ($j = 0; $j < $numberOfMessages; $j++) {
            $message = new Message();
            $message
                ->setDiscussion($discussion)
                ->setContent($faker->sentence())
                ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
                ->setAuthor($faker->randomElement([$specificUser, $secondUser]));
            $manager->persist($message);

            // add messages to the discussion
            $discussion->addMessage($message);
            $manager->persist($discussion);
        }
    }
}
