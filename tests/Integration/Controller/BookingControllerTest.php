<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookingControllerTest extends WebTestCase
{
    public function testCreateBooking(): void
    {
        $client = static::createClient();
        $uniqueId = time();
        $phone = '7' . substr(str_replace(['.', ' '], '', microtime()), 2, 10);

        // Регистрация
        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => "test{$uniqueId}@example.com",
            'phone' => $phone,
            'name' => 'Test User',
            'password' => 'password123'
        ]));

        // Логин
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone' => $phone,
            'password' => 'password123'
        ]));

        $response = json_decode($client->getResponse()->getContent(), true);
        $token = $response['token'] ?? '';

        // Создание бронирования
        $client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode([
            'house_id' => '2',
            'comment' => 'Test booking'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
    }

    public function testCreateBookingUnauthorized(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/bookings', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'house_id' => '2',
            'comment' => 'Test booking'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
