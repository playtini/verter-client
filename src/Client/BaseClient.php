<?php

namespace VerterClient\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class BaseClient
{
    /** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */
    private string $logLevel = LogLevel::DEBUG;

    public function __construct(
        private readonly string $baseUrl = 'https://verter.info',
        private readonly string $apiKey = '',
        private readonly bool $ignoreSslErrors = false,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @param array<string, string> $params
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected function sendRequest(string $path, array $params = [], string $method = 'POST'): string
    {
        $httpClient = HttpClient::create([
            'base_uri' => $this->baseUrl,
            'verify_peer' => !$this->ignoreSslErrors,
            'verify_host' => !$this->ignoreSslErrors,
        ]);

        $start = microtime(true);
        $response = $httpClient->request($method, $path, [
            'headers' => [
                'X-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ],
            'body' => $params,
        ]);
        $content = $response->getContent();
        $duration = microtime(true) - $start;

        $this->log(
            'verterclient.request',
            array_merge(['api_url' => $this->baseUrl . $path, 'duration' => $duration, 'status_code' => $response->getStatusCode()], $params)
        );

        return $content;
    }

    /**
     * @param array<string, mixed> $context
     */
    protected function log(string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($this->logLevel, $message, $context);
    }
}
