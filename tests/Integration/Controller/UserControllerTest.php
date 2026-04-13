<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testCreateUserSuccess(): void
    {
        $client = static::createClient();
        $uniqueId = time();
        // Просто генерируем 10 цифр и добавляем 7 в начало
        $phone = '7' . substr(str_replace(['.', ' '], '', microtime()), 2, 10);

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => "test{$uniqueId}@example.com",
            'phone' => $phone,
            'name' => 'Test User',
            'password' => 'password123'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Test User', $response['name']);
    }

    public function testCreateUserValidationError(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid',
            'phone' => '123',
            'name' => '',
            'password' => ''
        ]));

        $this->assertResponseStatusCodeSame(400);
    }
}
