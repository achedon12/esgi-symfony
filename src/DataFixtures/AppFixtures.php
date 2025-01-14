<?php

namespace App\DataFixtures;

use App\Entity\Offer;
use App\Entity\User;
use App\Enum\LanguageEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const int NUMBER_OF_USERS = 50;

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');
        $this->loadOffers($manager);
        $manager->flush();
        $this->loadUsers($manager, $faker);
        $manager->flush();
    }

    private function loadOffers(ObjectManager $manager): void
    {
        $offers = [
            ['name' => 'Basic', 'price' => 0, 'likeNumber' => 20, 'directMessageNumber' => 0],
            ['name' => 'Premium', 'price' => 9.99, 'likeNumber' => 50, 'directMessageNumber' => 5],
            ['name' => 'Pro', 'price' => 19.99, 'likeNumber' => -1, 'directMessageNumber' => -1],
        ];

        foreach ($offers as $offerData) {
            $offer = new Offer();
            $offer->setName($offerData['name'])
                ->setPrice($offerData['price'])
                ->setLikeNumber($offerData['likeNumber'])
                ->setDirectMessageNumber($offerData['directMessageNumber']);
            $manager->persist($offer);
        }
    }

    private function loadUsers(ObjectManager $manager, $faker): void
    {
        for ($i = 0; $i < self::NUMBER_OF_USERS; $i++) {
            $gender = $i < self::NUMBER_OF_USERS / 2 ? User::GENDER_MALE : User::GENDER_FEMALE;
            $user = $this->createUser($manager, $faker, $gender, $i);
            $manager->persist($user);
        }

        $manager->persist($this->createSpecificUser($manager, $faker, 'regular@gmail.com', 'regular', 'Premium', 'female'));
        $manager->persist($this->createSpecificUser($manager, $faker, 'admin@gmail.com', 'admin', 'Pro', 'female', ['ROLE_ADMIN'], 'people_images/male/admin.jpg'));
    }

    private function createUser($manager, $faker, string $gender, int $index): User
    {
        $user = new User();
        $firstname = $faker->firstName($gender === User::GENDER_MALE ? 'male' : 'female');
        $lastname = $faker->lastName();
        $email = strtolower($firstname . '.' . $lastname . '@gmail.com');
        $image = $this->getUserImage($gender, ($index % 25) + 1);

        $user->setEmail($email)
            ->setPassword($this->hasher->hashPassword($user, 'password'))
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
            ->setSexualOrientation($this->getSexualOrientation($gender, $faker))
            ->setOffer($manager->getRepository(Offer::class)->findOneBy(['name' => 'Basic']));

        if ($faker->boolean(90)) {
            $user->setUpdateDate(new \DateTimeImmutable($faker->dateTimeBetween($now, 'now')->format('Y-m-d H:i:s')));
        }

        return $user;
    }

    private function createSpecificUser($manager, $faker, string $email, string $password, string $offerName, string $sexualOrientation, array $roles = ['ROLE_USER'], string $image = 'https://thispersondoesnotexist.com/'): User
    {
        $user = new User();
        $user->setEmail($email)
            ->setPassword($this->hasher->hashPassword($user, $password))
            ->setCreationDate(new \DateTimeImmutable($faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s')))
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setLongitude($faker->longitude())
            ->setLatitude($faker->latitude())
            ->setImage($image)
            ->setRoles($roles)
            ->setBio($faker->sentence(10))
            ->setScore($faker->numberBetween(0, 100))
            ->setInterests($faker->words($faker->numberBetween(1, 5)))
            ->setGender($faker->randomElement([User::GENDER_MALE, User::GENDER_FEMALE, User::GENDER_OTHER]))
            ->setBirthdate(new \DateTimeImmutable($faker->dateTimeBetween('-100 years', '-18 years')->format('Y-m-d')))
            ->setLanguage($faker->randomElement([LanguageEnum::FRENCH->value, LanguageEnum::ENGLISH->value]))
            ->setSexualOrientation($sexualOrientation)
            ->setOffer($manager->getRepository(Offer::class)->findOneBy(['name' => $offerName]));

        return $user;
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
            return $random <= 80 ? User::GENDER_FEMALE : ($random <= 95 ? User::GENDER_MALE : User::GENDER_OTHER);
        } else {
            return $random <= 80 ? User::GENDER_MALE : ($random <= 95 ? User::GENDER_FEMALE : User::GENDER_OTHER);
        }
    }
}