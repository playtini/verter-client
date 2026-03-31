<?php

namespace VerterClient\Client;

use DateTimeImmutable;
use DateTimeInterface;
use VerterClient\Client\Exception\VerterFormatException;
use VerterClient\Client\Exception\VerterTransportException;
use VerterClient\Client\Object\RateItem;
use Throwable;

class RateClient extends BaseClient
{
    private const string RATE_PATH = '/api/v1/rates';

    /**
     * @throws VerterTransportException
     * @throws VerterFormatException
     */
    public function getRate(string $base, string $quote, string $channel, ?DateTimeInterface $date = null): ?RateItem
    {
        try {
            $content = $this->sendRequest(self::RATE_PATH, [
                'base' => $base,
                'quote' => $quote,
                'channel' => $channel,
                'date' => $date ? $date->format('Y-m-d H:i:s') : new DateTimeImmutable()->format('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            $this->log('verterclient.error.client', ['base' => $base, 'quote' => $quote, 'channel' => $channel, 'message' => $e->getMessage()]);

            throw new VerterTransportException($e->getMessage(), 0, $e);
        }

        try {
            /** @var array{data?: array{set_at: string, collected_at: string, channel: string, source: string, base: string, quote: string, rate: float|int|string, intermediate?: array<array<string, mixed>>}} $data */
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $this->log('verterclient.error.json', ['base' => $base, 'quote' => $quote, 'channel' => $channel, 'message' => $e->getMessage(), 'content' => mb_substr($content, 0, 1000)]);

            throw new VerterFormatException($e->getMessage(), 0, $e);
        }

        return ($data['data'] ?? false) ? RateItem::createFromJson($data['data']) : null;
    }
}
