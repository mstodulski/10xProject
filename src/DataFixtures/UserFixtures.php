<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures for User entities
 *
 * Creates 5 users:
 * - 4 consultants (konsultant1, konsultant2, konsultant3, konsultant4)
 * - 1 inspector (inspektor1)
 *
 * All users have the same password: "test" (hashed)
 *
 * NOTE: For demo/testing purposes only.
 * Plaintext password for all users: "test"
 */
class UserFixtures extends Fixture
{
    public const CONSULTANT_1_REFERENCE = 'user-consultant-1';
    public const CONSULTANT_2_REFERENCE = 'user-consultant-2';
    public const CONSULTANT_3_REFERENCE = 'user-consultant-3';
    public const CONSULTANT_4_REFERENCE = 'user-consultant-4';
    public const INSPECTOR_1_REFERENCE = 'user-inspector-1';

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create 4 consultants
        $consultant1 = $this->createUser(
            'konsultant1',
            PolishDataGenerator::getRandomName(),
            ['ROLE_CONSULTANT']
        );
        $manager->persist($consultant1);
        $this->addReference(self::CONSULTANT_1_REFERENCE, $consultant1);

        $consultant2 = $this->createUser(
            'konsultant2',
            PolishDataGenerator::getRandomName(),
            ['ROLE_CONSULTANT']
        );
        $manager->persist($consultant2);
        $this->addReference(self::CONSULTANT_2_REFERENCE, $consultant2);

        $consultant3 = $this->createUser(
            'konsultant3',
            PolishDataGenerator::getRandomName(),
            ['ROLE_CONSULTANT']
        );
        $manager->persist($consultant3);
        $this->addReference(self::CONSULTANT_3_REFERENCE, $consultant3);

        $consultant4 = $this->createUser(
            'konsultant4',
            PolishDataGenerator::getRandomName(),
            ['ROLE_CONSULTANT']
        );
        $manager->persist($consultant4);
        $this->addReference(self::CONSULTANT_4_REFERENCE, $consultant4);

        // Create 1 inspector
        $inspector = $this->createUser(
            'inspektor1',
            PolishDataGenerator::getRandomName(),
            ['ROLE_INSPECTOR']
        );
        $manager->persist($inspector);
        $this->addReference(self::INSPECTOR_1_REFERENCE, $inspector);

        $manager->flush();
    }

    private function createUser(string $username, string $name, array $roles): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setName($name);
        $user->setRoles($roles);
        $user->setIsActive(true);

        // Hash password "test"
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);

        return $user;
    }
}
