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

    /**
     * @var array|RateItem[]
     */
    private array $intermediate;

    private function __construct(
        DateTimeInterface $setAt,
        DateTimeInterface $collectedAt,
        string $channel,
        string $source,
        string $base,
        string $quote,
        float $rate,
        array $intermediate = []
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
        $intermediates = [];
        if (isset($data['intermediate'])) {
            foreach ($data['intermediate'] as $inter) {
                $intermediates[] = self::createFromJson($inter);
            }
        }
        return (new self(
            new DateTime($data['set_at']),
            new DateTime($data['collected_at']),
            $data['channel'],
            $data['source'],
            $data['base'],
            $data['quote'],
            (float)$data['rate'],
            $intermediates
        ));
    }

    public function jsonSerialize(): array
    {
        $intermediates = [];
        if ($this->intermediate) {
            foreach ($this->intermediate as $inter) {
                $intermediates[] = $inter->jsonSerialize();
            }
        }
        return [
            'set_at' => $this->setAt->format('Y-m-d H:i:s'),
            'collected_at' => $this->collectedAt->format('Y-m-d H:i:s'),
            'channel' => $this->channel,
            'source' => $this->source,
            'base' => $this->base,
            'quote' => $this->quote,
            'rate' => $this->rate,
            'intermediate' => $intermediates
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
     * @return array|RateItem[]
     */
    public function getIntermediate(): array
    {
        return $this->intermediate;
    }
}
