<?php

namespace VerterClient\Tests\Client;

use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use VerterClient\Client\Exception\VerterFormatException;
use VerterClient\Client\Exception\VerterTransportException;
use VerterClient\Client\Object\RateItem;
use VerterClient\Client\RateClient;

class RateClientTest extends TestCase
{
    public function testGetRateReturnsRateItem(): void
    {
        $responseData = [
            'data' => [
                'set_at' => '2023-01-01 12:00:00',
                'collected_at' => '2023-01-01 11:00:00',
                'channel' => 'main',
                'source' => 'ecb',
                'base' => 'USD',
                'quote' => 'EUR',
                'rate' => 0.92,
                'intermediate' => [],
            ],
        ];

        $client = $this->createClientWithResponse(json_encode($responseData));
        $result = $client->getRate('USD', 'EUR', 'main');

        self::assertInstanceOf(RateItem::class, $result);
        self::assertSame('USD', $result->getBase());
        self::assertSame('EUR', $result->getQuote());
        self::assertSame(0.92, $result->getRate());
    }

    public function testGetRateWithDate(): void
    {
        $responseData = [
            'data' => [
                'set_at' => '2023-06-15 12:00:00',
                'collected_at' => '2023-06-15 11:00:00',
                'channel' => 'main',
                'source' => 'ecb',
                'base' => 'USD',
                'quote' => 'EUR',
                'rate' => 0.91,
                'intermediate' => [],
            ],
        ];

        $client = $this->createClientWithResponse(json_encode($responseData));
        $date = new DateTime('2023-06-15 12:00:00');
        $result = $client->getRate('USD', 'EUR', 'main', $date);

        self::assertInstanceOf(RateItem::class, $result);
        self::assertSame(0.91, $result->getRate());
    }

    public function testGetRateReturnsNullWhenNoData(): void
    {
        $client = $this->createClientWithResponse(json_encode(['data' => null]));

        self::assertNull($client->getRate('USD', 'EUR', 'main'));
    }

    public function testGetRateReturnsNullWhenDataKeyMissing(): void
    {
        $client = $this->createClientWithResponse(json_encode(['status' => 'ok']));

        self::assertNull($client->getRate('USD', 'EUR', 'main'));
    }

    public function testGetRateThrowsTransportExceptionOnHttpError(): void
    {
        $client = $this->createClientWithException(new RuntimeException('Connection refused'));

        $this->expectException(VerterTransportException::class);
        $this->expectExceptionMessage('Connection refused');

        $client->getRate('USD', 'EUR', 'main');
    }

    public function testGetRateThrowsFormatExceptionOnInvalidJson(): void
    {
        $client = $this->createClientWithResponse('not json {{{');

        $this->expectException(VerterFormatException::class);

        $client->getRate('USD', 'EUR', 'main');
    }

    public function testTransportExceptionIsLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with(
                self::anything(),
                'verterclient.error.client',
                self::callback(fn(array $ctx) => $ctx['base'] === 'USD'
                    && $ctx['quote'] === 'EUR'
                    && $ctx['channel'] === 'main'
                    && str_contains($ctx['message'], 'timeout')
                ),
            );

        $client = $this->createClientWithException(new RuntimeException('timeout'), $logger);

        try {
            $client->getRate('USD', 'EUR', 'main');
        } catch (VerterTransportException) {
        }
    }

    public function testFormatExceptionIsLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())
            ->method('log');

        $client = $this->createClientWithResponse('broken json', $logger);

        try {
            $client->getRate('USD', 'EUR', 'main');
        } catch (VerterFormatException) {
        }
    }

    private function createClientWithResponse(string $response, ?LoggerInterface $logger = null): RateClient
    {
        return new class($response, $logger) extends RateClient {
            public function __construct(
                private readonly string $stubbedResponse,
                ?LoggerInterface $logger = null,
            ) {
                parent::__construct('https://verter.info', 'test-key', false, ...($logger ? [$logger] : []));
            }

            protected function sendRequest(string $path, array $params = [], string $method = 'POST'): string
            {
                return $this->stubbedResponse;
            }
        };
    }

    private function createClientWithException(\Throwable $exception, ?LoggerInterface $logger = null): RateClient
    {
        return new class($exception, $logger) extends RateClient {
            public function __construct(
                private readonly \Throwable $stubbedException,
                ?LoggerInterface $logger = null,
            ) {
                parent::__construct('https://verter.info', 'test-key', false, ...($logger ? [$logger] : []));
            }

            protected function sendRequest(string $path, array $params = [], string $method = 'POST'): string
            {
                throw $this->stubbedException;
            }
        };
    }
}
