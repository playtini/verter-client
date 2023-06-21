<?php

namespace VerterClient\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

abstract class BaseClient
{
    private string $apiKey;

    private string $baseUrl;

    private bool $ignoreSslErrors;

    private LoggerInterface $logger;

    private string $logLevel = LogLevel::DEBUG;

    public function __construct(
        string $baseUrl = 'https://verter.info',
        string $apiKey = '',
        bool $ignoreSslErrors = false,
        LoggerInterface $logger = null
    ) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->ignoreSslErrors = $ignoreSslErrors;
        $this->logger = $logger ?? new NullLogger();
    }

    public function setLogLevel(string $logLevel): void
    {
        $this->logLevel = $logLevel;
    }

    /**
     * @param string $path
     * @param array $params
     * @param string $method
     * @return string
     * @throws GuzzleException
     */
    protected function sendRequest(string $path, array $params = [], string $method = 'POST'): string
    {
        $httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => !$this->ignoreSslErrors,
        ]);

        $start = microtime(true);
        $response = $httpClient->request($method, $path, [
            'headers' => [
                'X-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ],
            'form_params' => $params,
        ]);
        $duration = microtime(true) - $start;

        $this->log(
            'verterclient.request',
            array_merge(['api_url' => $this->baseUrl.$path, 'duration' => $duration, 'status_code' => $response->getStatusCode()], $params)
        );

        return $response->getBody()->getContents();
    }

    /** @param string|\Stringable $message */
    protected function log($message, array $context = []): void
    {
        $this->logger->log($this->logLevel, $message, $context);
    }
}
