<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/bookings', name: 'api_bookings_')]
class BookingController extends AbstractController
{
    public function __construct(private CsvService $csvService) {}

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new booking',
        description: 'Creates a booking for a house for the authenticated user',
        requestBody: new OA\RequestBody(
            description: 'Booking data',
            required: true,
            content: new OA\JsonContent(
                required: ['house_id', 'comment'],
                properties: [
                    new OA\Property(property: 'house_id', type: 'string', example: '2'),
                    new OA\Property(property: 'comment', type: 'string', example: 'Хочу домик у моря'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Booking created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', example: '7a8b3c5d2e1f4a6b8c0d2e4f'),
                        new OA\Property(property: 'house_id', type: 'string', example: '2'),
                        new OA\Property(property: 'user_id', type: 'integer', example: 1),
                        new OA\Property(property: 'user_phone', type: 'string', example: '79141234567'),
                        new OA\Property(property: 'comment', type: 'string', example: 'Хочу домик у моря'),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Validation error'),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 404, description: 'House not found'),
        ]
    )]
    public function createBooking(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true);

        if (!isset($data['house_id'], $data['comment'])) {
            return new JsonResponse([
                'error' => 'house_id and comment are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $houses = $this->csvService->read('houses.csv');
        $house = null;
        foreach ($houses as $h) {
            if ($h['id'] == $data['house_id']) {
                $house = $h;
                break;
            }
        }

        if (!$house) {
            return new JsonResponse(['error' => 'House not found'], Response::HTTP_NOT_FOUND);
        }

        if ($house['available'] != '1') {
            return new JsonResponse(['error' => 'House is not available'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        $booking = [
            'id' => Uuid::v4()->toRfc4122(),
            'house_id' => $data['house_id'],
            'user_id' => $user->getId(),
            'user_phone' => $user->getPhone(),
            'comment' => $data['comment'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->csvService->append('bookings.csv', [
            $booking['id'],
            $booking['house_id'],
            $booking['user_id'],
            $booking['user_phone'],
            $booking['comment'],
            $booking['created_at']
        ]);

        return new JsonResponse($booking, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Update booking comment',
        description: 'Updates the comment of an existing booking',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Booking ID',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Updated comment',
            required: true,
            content: new OA\JsonContent(
                required: ['comment'],
                properties: [
                    new OA\Property(property: 'comment', type: 'string', example: 'Передумал, хочу другой домик'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Booking updated successfully'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Not authenticated'),
            new OA\Response(response: 404, description: 'Booking not found'),
        ]
    )]
    public function updateBooking(string $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $data = json_decode($request->getContent(), true);

        if (!isset($data['comment'])) {
            return new JsonResponse(['error' => 'comment is required'], Response::HTTP_BAD_REQUEST);
        }

        $bookings = $this->csvService->read('bookings.csv');

        $found = false;
        foreach ($bookings as &$booking) {
            if ($booking['id'] === $id) {
                $booking['comment'] = $data['comment'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            return new JsonResponse(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $csvData = [];
        foreach ($bookings as $booking) {
            $csvData[] = [
                $booking['id'],
                $booking['house_id'],
                $booking['user_id'] ?? '',
                $booking['user_phone'] ?? '',
                $booking['comment'],
                $booking['created_at']
            ];
        }

        $this->csvService->write('bookings.csv', $csvData, ['id', 'house_id', 'user_id', 'user_phone', 'comment', 'created_at']);

        return new JsonResponse(['message' => 'Booking updated successfully']);
    }
}
