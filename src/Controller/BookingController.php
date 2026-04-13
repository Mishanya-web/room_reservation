<?php

namespace App\Controller;

use App\Service\CsvService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/bookings', name: 'api_bookings_')]
class BookingController
{
    private CsvService $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Валидация
        if (!isset($data['phone']) || !isset($data['house_id'])) {
            return new JsonResponse([
                'error' => 'phone and house_id are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Проверяем, существует ли домик и свободен ли он
        $houses = $this->csvService->read('houses.csv');
        $house = null;
        foreach ($houses as $h) {
            if ($h['id'] == $data['house_id']) {
                $house = $h;
                break;
            }
        }

        if (!$house) {
            return new JsonResponse([
                'error' => 'House not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($house['available'] != '1') {
            return new JsonResponse([
                'error' => 'House is not available for booking'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Создаем бронирование
        $booking = [
            'id' => Uuid::v4()->toRfc4122(),
            'house_id' => $data['house_id'],
            'phone' => $data['phone'],
            'comment' => $data['comment'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Записываем в CSV
        $this->csvService->append('bookings.csv', [
            $booking['id'],
            $booking['house_id'],
            $booking['phone'],
            $booking['comment'],
            $booking['created_at']
        ]);

        return new JsonResponse($booking, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function updateBooking(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['comment'])) {
            return new JsonResponse([
                'error' => 'comment is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Читаем все бронирования
        $bookings = $this->csvService->read('bookings.csv');

        // Ищем нужное бронирование
        $found = false;
        foreach ($bookings as &$booking) {
            if ($booking['id'] === $id) {
                $booking['comment'] = $data['comment'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            return new JsonResponse([
                'error' => 'Booking not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Перезаписываем весь файл
        $csvData = [];
        foreach ($bookings as $booking) {
            $csvData[] = [
                $booking['id'],
                $booking['house_id'],
                $booking['phone'],
                $booking['comment'],
                $booking['created_at']
            ];
        }

        $this->csvService->write('bookings.csv', $csvData, ['id', 'house_id', 'phone', 'comment', 'created_at']);

        return new JsonResponse(['message' => 'Booking updated successfully']);
    }
}
