<?php

namespace VerterClient\Client\Object;

use DateTime;
use DateTimeInterface;
use Throwable;

class RateItem
{
    private DateTimeInterface $setAt;

    private DateTimeInterface $collectedAt;

    private string $channel;

    private string $source;

    private string $base;

    private string $quote;

    private float $rate;

    private ?RateItem $intermediate;

    private function __construct(
        DateTimeInterface $setAt,
        DateTimeInterface $collectedAt,
        string $channel,
        string $source,
        string $base,
        string $quote,
        float $rate,
        ?RateItem $intermediate = null
    ){
        $this->setAt = $setAt;
        $this->collectedAt = $collectedAt;
        $this->channel = $channel;
        $this->source = $source;
        $this->base = $base;
        $this->quote = $quote;
        $this->rate = $rate;
        $this->intermediate = $intermediate;
    }

    /**
     * @param array $data
     * @return static
     * @throws Throwable
     */
    public static function createFromJson(array $data): self
    {
        return (new self(
            new DateTime($data['set_at']),
            new DateTime($data['collected_at']),
            $data['channel'],
            $data['source'],
            $data['base'],
            $data['quote'],
            (float)$data['rate'],
            $data['intermediate'] ?
                self::createFromJson($data['intermediate']) :
                null
        ));
    }

    public function jsonSerialize(): array
    {
        return [
            'set_at' => $this->setAt,
            'collected_at' => $this->collectedAt,
            'channel' => $this->channel,
            'source' => $this->source,
            'base' => $this->base,
            'quote' => $this->quote,
            'rate' => $this->rate,
            'intermediate' => $this->intermediate ? $this->intermediate->jsonSerialize() : null
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

    public function getIntermediate(): ?RateItem
    {
        return $this->intermediate;
    }
}
