<?php

namespace VerterClient\Tests\Client;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use VerterClient\Client\RateClient;

class BaseClientTest extends TestCase
{
    public function testDefaultLogLevelIsDebug(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::DEBUG, self::anything(), self::anything());

        $client = $this->createClientWithLogger($logger);
        $this->invokeLog($client, 'test message');
    }

    public function testSetLogLevel(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(LogLevel::WARNING, 'test message', []);

        $client = $this->createClientWithLogger($logger);
        $client->setLogLevel(LogLevel::WARNING);
        $this->invokeLog($client, 'test message');
    }

    public function testLogPassesContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(self::anything(), 'msg', ['key' => 'value']);

        $client = $this->createClientWithLogger($logger);
        $this->invokeLog($client, 'msg', ['key' => 'value']);
    }

    private function createClientWithLogger(LoggerInterface $logger): RateClient
    {
        return new RateClient(logger: $logger);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function invokeLog(RateClient $client, string $message, array $context = []): void
    {
        $ref = new \ReflectionMethod($client, 'log');
        $ref->invoke($client, $message, $context);
    }
}
