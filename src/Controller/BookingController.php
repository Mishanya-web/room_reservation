<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvService;
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
