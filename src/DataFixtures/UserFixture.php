<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    public const FIRST_USER_REFERENCE = 'FIRST_USER';

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $firstUser = $this->createUser('john.brown@test.com', 'John', 'Brown', 'ROLE_ADMIN', 'brown');

        $manager->persist($firstUser);
        $manager->persist($this->createUser('sam.green@test.com', 'Sam', 'Green', 'ROLE_USER', 'green'));
        $manager->persist($this->createUser('alan.white@test.com', 'Alan', 'White', 'ROLE_USER', 'white'));
        $manager->flush();

        $this->addReference(self::FIRST_USER_REFERENCE, $firstUser);
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string $password
     *
     * @return User
     */
    private function createUser(
        string $email,
        string $firstName,
        string $lastName,
        string $role,
        string $password
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles([$role]);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        return $user;
    }
}
