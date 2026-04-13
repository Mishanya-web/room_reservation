<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        $uniqueId = time();
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

    public function testLoginSuccess(): void
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

        $this->assertResponseStatusCodeSame(200);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
    }

    public function testLoginInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone' => '79141234560',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
