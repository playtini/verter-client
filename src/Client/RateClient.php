<?php

namespace VerterClient\Client;

use DateTimeInterface;
use DateTime;
use VerterClient\Client\Exception\VerterException;
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
     * @return RateItem|null
     * @throws Throwable
     */
    public function getRate(string $base, string $quote, string $channel, ?DateTimeInterface $date = null): ?RateItem
    {
        try {
            $content = $this->sendRequest(self::RATE_PATH, [
                'base' => $base,
                'quote' => $quote,
                'channel' => $channel,
                'date' => $date ? $date->format('Y-m-d H:i:s') : (new DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            throw new VerterException('Something went wrong during extraction currency rate');
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new VerterException('Wrong object from currency converter');
        }

        return $data ? RateItem::createFromJson($data) : null;
    }
}
