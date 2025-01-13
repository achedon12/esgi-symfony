<?php

namespace App\DataFixtures;

use App\Entity\Discussion;
use App\Entity\Message;
use App\Entity\User;
use App\Enum\LanguageEnum;
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
        $faker = Factory::create('en_US');
        $this->loadUsers($manager, $faker);
        $manager->flush();

        // load more data for dev environment
        //if (getenv('APP_ENV') === 'dev') {
        //$this->loadDiscussions($manager, $faker);
        //}
        $manager->flush();
    }

    private function getUserImage(string $gender, int $index): string
    {
        $directory = $gender === User::GENDER_MALE ? 'assets/people_images/male/' : 'assets/people_images/female/';
        $image = $directory . strtolower($gender) . '_' . $index . '.jpg';
        return substr($image, 7);
    }

    private function getSexualOrientation(string $gender, $faker): string
    {
        $random = $faker->numberBetween(1, 100);
        if ($gender === User::GENDER_MALE) {
            if ($random <= 80) {
                return User::GENDER_FEMALE;
            } elseif ($random <= 95) {
                return User::GENDER_MALE;
            } else {
                return User::GENDER_OTHER;
            }
        } else {
            if ($random <= 80) {
                return User::GENDER_MALE;
            } elseif ($random <= 95) {
                return User::GENDER_FEMALE;
            } else {
                return User::GENDER_OTHER;
            }
        }
    }


    private function loadUsers(ObjectManager $manager, $faker): void
    {
        // create regular users
        for ($i = 0; $i < self::NUMBER_OF_USERS; $i++) {
            $regularUser = new User();
            $gender = $i < self::NUMBER_OF_USERS / 2 ? User::GENDER_MALE : User::GENDER_FEMALE;
            $firstname = $faker->firstName($gender === User::GENDER_MALE ? 'male' : 'female');
            $lastname = $faker->lastName();
            $email = strtolower($firstname . '.' . $lastname . '@gmail.com');
            $image = $this->getUserImage($gender, ($i % 25) + 1);

            $regularUser
                ->setEmail($email)
                ->setPassword($this->hasher->hashPassword($regularUser, 'regular'))
                ->setCreationDate(new \DateTimeImmutable($now = $faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
                ->setFirstname($firstname)
                ->setLastname($lastname)
                ->setLongitude($faker->longitude())
                ->setLatitude($faker->latitude())
                ->setImage($image)
                ->setRoles(['ROLE_USER'])
                ->setBio($faker->sentence(10))
                ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')))
                ->setGender($gender)
                ->setInterests($faker->words($faker->numberBetween(1, 5)))
                ->setScore($faker->numberBetween(0, 100))
                ->setLanguage($faker->randomElement([LanguageEnum::FRENCH->value, LanguageEnum::ENGLISH->value]))
                ->setSexualOrientation($this->getSexualOrientation($gender, $faker));

            if ($faker->boolean(90)) {
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
            ->setImage("people_images/male/admin.jpg")
            ->setRoles(['ROLE_ADMIN'])
            ->setBio($faker->sentence(10))
            ->setScore($faker->numberBetween(0, 100))
            ->setInterests($faker->words($faker->numberBetween(1, 5)))
            ->setGender($faker->randomElement([User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_OTHER]))
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')))
            ->setLanguage($faker->randomElement([LanguageEnum::FRENCH->value, LanguageEnum::ENGLISH->value]))
            ->setSexualOrientation('female');

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
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')))
            ->setLanguage($faker->randomElement([LanguageEnum::FRENCH->value, LanguageEnum::ENGLISH->value]))
            ->setSexualOrientation('female');
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
