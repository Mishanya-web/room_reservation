<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createUser(string $email, string $phone, string $name): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setName($name);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
