<?php

declare(strict_types=1);

namespace Togglr\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Togglr\Sdk\BackoffConfig;
use Togglr\Sdk\ClientConfig;

class ClientConfigTest extends TestCase
{
    public function testDefaultConfig(): void
    {
        $config = ClientConfig::default('test-api-key');

        $this->assertEquals('test-api-key', $config->getApiKey());
        $this->assertEquals('http://localhost:8090', $config->getBaseUrl());
        $this->assertEquals(0.8, $config->getTimeout());
        $this->assertEquals(2, $config->getRetries());
        $this->assertInstanceOf(BackoffConfig::class, $config->getBackoff());
        $this->assertFalse($config->isCacheEnabled());
        $this->assertEquals(100, $config->getCacheMaxSize());
        $this->assertEquals(5, $config->getCacheTtl());
        $this->assertNull($config->getLogger());
    }

    public function testWithBaseUrl(): void
    {
        $config = ClientConfig::default('test-api-key')->withBaseUrl('https://api.example.com');
        $this->assertEquals('https://api.example.com', $config->getBaseUrl());
    }

    public function testWithTimeout(): void
    {
        $config = ClientConfig::default('test-api-key')->withTimeout(2.0);
        $this->assertEquals(2.0, $config->getTimeout());
    }

    public function testWithRetries(): void
    {
        $config = ClientConfig::default('test-api-key')->withRetries(5);
        $this->assertEquals(5, $config->getRetries());
    }

    public function testWithCache(): void
    {
        $config = ClientConfig::default('test-api-key')->withCache(true, 1000, 60);

        $this->assertTrue($config->isCacheEnabled());
        $this->assertEquals(1000, $config->getCacheMaxSize());
        $this->assertEquals(60, $config->getCacheTtl());
    }

    public function testWithBackoff(): void
    {
        $backoff = new BackoffConfig(0.5, 10.0, 1.5);
        $config = ClientConfig::default('test-api-key')->withBackoff($backoff);

        $this->assertSame($backoff, $config->getBackoff());
    }

    public function testWithLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $config = ClientConfig::default('test-api-key')->withLogger($logger);

        $this->assertSame($logger, $config->getLogger());
    }

    public function testChaining(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $backoff = new BackoffConfig(0.2, 5.0, 1.8);

        $config = ClientConfig::default('test-api-key')
            ->withBaseUrl('https://api.example.com')
            ->withTimeout(3.0)
            ->withRetries(4)
            ->withCache(true, 2000, 120)
            ->withBackoff($backoff)
            ->withLogger($logger);

        $this->assertEquals('test-api-key', $config->getApiKey());
        $this->assertEquals('https://api.example.com', $config->getBaseUrl());
        $this->assertEquals(3.0, $config->getTimeout());
        $this->assertEquals(4, $config->getRetries());
        $this->assertTrue($config->isCacheEnabled());
        $this->assertEquals(2000, $config->getCacheMaxSize());
        $this->assertEquals(120, $config->getCacheTtl());
        $this->assertSame($backoff, $config->getBackoff());
        $this->assertSame($logger, $config->getLogger());
    }
}
