<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController  // ← добавь extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['phone'], $data['name'])) {
            return new JsonResponse(['error' => 'email, phone, name are required'], 400);
        }

        $user = $this->userService->createUser(
            $data['email'],
            $data['phone'],
            $data['name']
        );

        // Теперь $this->json() работает, потому что мы унаследовались от AbstractController
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'name' => $user->getName(),
        ], 201);
    }
}
