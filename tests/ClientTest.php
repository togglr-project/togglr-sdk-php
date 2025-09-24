<?php

declare(strict_types=1);

namespace Togglr\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Togglr\Sdk\Client;
use Togglr\Sdk\ClientConfig;

class ClientTest extends TestCase
{
    public function testClientInitialization(): void
    {
        $config = ClientConfig::default('test-api-key');
        $client = new Client($config);

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testHealthCheckSuccess(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testHealthCheckFailure(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testEvaluateSuccess(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testIsEnabledSuccess(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testIsEnabledNotFound(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testIsEnabledOrDefaultSuccess(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }

    public function testIsEnabledOrDefaultError(): void
    {
        // This would require mocking the HTTP client
        $this->markTestSkipped('Requires HTTP client mocking');
    }
}
