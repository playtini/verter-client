<?php

namespace VerterClient\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

abstract class BaseClient
{
    private string $apiKey;

    private string $baseUrl;

    private bool $ignoreSslErrors;

    public function __construct(string $baseUrl, string $apiKey, bool $ignoreSslErrors = false)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->ignoreSslErrors = $ignoreSslErrors;
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
            'verify' => !$this->ignoreSslErrors
        ]);

        $response = $httpClient->request($method, $path, [
            'headers' => [
                'X-Api-Key' => $this->apiKey,
                'Accept'    => 'application/json',
            ],
            'form_params' => $params
        ]);

        return $response->getBody()->getContents();
    }
}
