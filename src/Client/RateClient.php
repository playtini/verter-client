<?php

namespace VerterClient\Client;

use DateTimeInterface;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use VerterClient\Client\Object\RateItem;
use Throwable;

class RateClient extends BaseClient
{
    private const RATE_PATH = '/api/v1/rates';

    /**
     * @param string $base
     * @param string $quote
     * @param string $channel
     * @param DateTimeInterface|null $date
     * @return RateItem
     * @throws GuzzleException
     * @throws JsonException
     * @throws Throwable
     */
    public function getRate(string $base, string $quote, string $channel, ?DateTimeInterface $date = null): RateItem
    {
        $content = $this->sendRequest(self::RATE_PATH, [
            'base' => $base,
            'quote' => $quote,
            'channel' => $channel,
            'date' => $date ? $date->format('Y-m-d H:i:s') : (new DateTime())->format('Y-m-d H:i:s')
        ]);

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return RateItem::createFromJson($data);
    }
}
