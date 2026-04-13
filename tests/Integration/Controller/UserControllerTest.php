<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();

        // Получаем EntityManager через клиент
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Очищаем таблицу
        $connection = $entityManager->getConnection();
        $connection->executeStatement('TRUNCATE users RESTART IDENTITY CASCADE');

        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'phone' => '79141234567',
            'name' => 'Test User'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('test@example.com', $response['email']);

        $entityManager->close();
    }
}
