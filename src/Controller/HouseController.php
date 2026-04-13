<?php

namespace App\Controller;

use App\Service\CsvService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/houses', name: 'api_houses_')]
class HouseController
{
    private CsvService $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('/available', name: 'available', methods: ['GET'])]
    public function getAvailableHouses(): JsonResponse
    {
        $houses = $this->csvService->read('houses.csv');

        $available = array_filter($houses, function($house) {
            return $house['available'] == '1';
        });

        return new JsonResponse(array_values($available));
    }
}
