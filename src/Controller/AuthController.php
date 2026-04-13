<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['phone'], $data['name'], $data['password'])) {
            return new JsonResponse([
                'error' => 'email, phone, name, password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if user exists
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['phone' => $data['phone']]);

        if ($existingUser) {
            return new JsonResponse([
                'error' => 'User with this phone already exists'
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPhone($data['phone']);
        $user->setName($data['name']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'name' => $user->getName(),
            'roles' => $user->getRoles()
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This route is handled by Symfony's json_login
        // This method will never be executed
        return new JsonResponse(['message' => 'Login endpoint'], Response::HTTP_OK);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // This route is handled by Symfony's logout
        return new JsonResponse(['message' => 'Logged out successfully'], Response::HTTP_OK);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'name' => $user->getName(),
            'roles' => $user->getRoles()
        ]);
    }
}
