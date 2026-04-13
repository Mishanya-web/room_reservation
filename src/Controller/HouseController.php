<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/houses', name: 'api_houses_')]
class HouseController extends AbstractController
{
    public function __construct(private CsvService $csvService) {}

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
