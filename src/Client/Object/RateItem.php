<?php

namespace VerterClient\Client\Object;

use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;

readonly class RateItem implements JsonSerializable
{
    /**
     * @param array<RateItem> $intermediate
     */
    private function __construct(
        private DateTimeInterface $setAt,
        private DateTimeInterface $collectedAt,
        private string $channel,
        private string $source,
        private string $base,
        private string $quote,
        private float $rate,
        private array $intermediate = [],
    ) {
    }

    /**
     * @param array{
     *     set_at: string,
     *     collected_at: string,
     *     channel: string,
     *     source: string,
     *     base: string,
     *     quote: string,
     *     rate: float|int|string,
     *     intermediate?: array<array<string, mixed>>
     * } $data
     * @throws \DateMalformedStringException
     */
    public static function createFromJson(array $data): self
    {
        $intermediates = [];
        if (isset($data['intermediate'])) {
            foreach ($data['intermediate'] as $inter) {
                /** @var array{set_at: string, collected_at: string, channel: string, source: string, base: string, quote: string, rate: float|int|string, intermediate?: array<array<string, mixed>>} $inter */
                $intermediates[] = self::createFromJson($inter);
            }
        }

        return new self(
            new DateTimeImmutable($data['set_at']),
            new DateTimeImmutable($data['collected_at']),
            $data['channel'],
            $data['source'],
            $data['base'],
            $data['quote'],
            (float) $data['rate'],
            $intermediates,
        );
    }

    /**
     * @return array{
     *     set_at: string,
     *     collected_at: string,
     *     channel: string,
     *     source: string,
     *     base: string,
     *     quote: string,
     *     rate: float,
     *     intermediate: array<array<string, mixed>>
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'set_at' => $this->setAt->format('Y-m-d H:i:s'),
            'collected_at' => $this->collectedAt->format('Y-m-d H:i:s'),
            'channel' => $this->channel,
            'source' => $this->source,
            'base' => $this->base,
            'quote' => $this->quote,
            'rate' => $this->rate,
            'intermediate' => array_map(
                static fn(RateItem $item) => $item->jsonSerialize(),
                $this->intermediate,
            ),
        ];
    }

    public function getSetAt(): DateTimeInterface
    {
        return $this->setAt;
    }

    public function getCollectedAt(): DateTimeInterface
    {
        return $this->collectedAt;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getBase(): string
    {
        return $this->base;
    }

    public function getQuote(): string
    {
        return $this->quote;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @return array<RateItem>
     */
    public function getIntermediate(): array
    {
        return $this->intermediate;
    }
}
