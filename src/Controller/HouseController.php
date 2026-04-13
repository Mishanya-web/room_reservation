<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/houses', name: 'api_houses_')]
class HouseController extends AbstractController
{
    public function __construct(private CsvService $csvService) {}

    #[Route('/available', name: 'available', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get available houses',
        description: 'Returns a list of all houses that are currently available for booking',
        tags: ['Houses'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of available houses',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string', example: '2'),
                            new OA\Property(property: 'name', type: 'string', example: 'Эконом вариант'),
                            new OA\Property(property: 'amenities', type: 'string', example: 'WC'),
                            new OA\Property(property: 'beds', type: 'string', example: '1'),
                            new OA\Property(property: 'distance', type: 'string', example: '30'),
                            new OA\Property(property: 'price', type: 'string', example: '1500'),
                            new OA\Property(property: 'available', type: 'string', example: '1'),
                        ],
                        type: 'object'
                    )
                )
            ),
        ]
    )]
    public function getAvailableHouses(): JsonResponse
    {
        $houses = $this->csvService->read('houses.csv');

        $available = array_filter($houses, function($house) {
            return $house['available'] == '1';
        });

        return new JsonResponse(array_values($available));
    }
}
