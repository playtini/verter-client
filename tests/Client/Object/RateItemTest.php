<?php

namespace VerterClient\Tests\Client\Object;

use VerterClient\Client\Object\RateItem;
use PHPUnit\Framework\TestCase;

class RateItemTest extends TestCase
{
    public function testCreateFromJson(): void
    {
        $data = [
            'set_at' => '2022-10-20 10:20:30',
            'collected_at' => '2022-10-20 00:10:20',
            'channel' => 'c1',
            'source' => 's1',
            'base' => 'USD',
            'quote' => 'EUR',
            'rate' => 1.5,
            'intermediate' => [],
        ];
        $item = RateItem::createFromJson($data);

        self::assertSame($data, $item->jsonSerialize());
    }

    public function testGetters(): void
    {
        $data = [
            'set_at' => '2023-05-15 12:00:00',
            'collected_at' => '2023-05-15 11:00:00',
            'channel' => 'main',
            'source' => 'ecb',
            'base' => 'EUR',
            'quote' => 'GBP',
            'rate' => 0.87,
        ];
        $item = RateItem::createFromJson($data);

        self::assertSame('2023-05-15 12:00:00', $item->getSetAt()->format('Y-m-d H:i:s'));
        self::assertSame('2023-05-15 11:00:00', $item->getCollectedAt()->format('Y-m-d H:i:s'));
        self::assertSame('main', $item->getChannel());
        self::assertSame('ecb', $item->getSource());
        self::assertSame('EUR', $item->getBase());
        self::assertSame('GBP', $item->getQuote());
        self::assertSame(0.87, $item->getRate());
        self::assertSame([], $item->getIntermediate());
    }

    public function testCreateFromJsonWithIntermediates(): void
    {
        $data = [
            'set_at' => '2022-10-20 10:20:30',
            'collected_at' => '2022-10-20 00:10:20',
            'channel' => 'c1',
            'source' => 's1',
            'base' => 'USD',
            'quote' => 'JPY',
            'rate' => 149.5,
            'intermediate' => [
                [
                    'set_at' => '2022-10-20 10:20:30',
                    'collected_at' => '2022-10-20 00:10:20',
                    'channel' => 'c1',
                    'source' => 's1',
                    'base' => 'USD',
                    'quote' => 'EUR',
                    'rate' => 0.95,
                ],
                [
                    'set_at' => '2022-10-20 10:20:30',
                    'collected_at' => '2022-10-20 00:10:20',
                    'channel' => 'c1',
                    'source' => 's1',
                    'base' => 'EUR',
                    'quote' => 'JPY',
                    'rate' => 157.37,
                ],
            ],
        ];

        $item = RateItem::createFromJson($data);

        self::assertCount(2, $item->getIntermediate());
        self::assertInstanceOf(RateItem::class, $item->getIntermediate()[0]);
        self::assertSame('USD', $item->getIntermediate()[0]->getBase());
        self::assertSame('EUR', $item->getIntermediate()[0]->getQuote());
        self::assertSame('EUR', $item->getIntermediate()[1]->getBase());
        self::assertSame('JPY', $item->getIntermediate()[1]->getQuote());

        $serialized = $item->jsonSerialize();
        self::assertCount(2, $serialized['intermediate']);
        self::assertSame(0.95, $serialized['intermediate'][0]['rate']);
        self::assertSame(157.37, $serialized['intermediate'][1]['rate']);
    }

    public function testCreateFromJsonWithoutIntermediateKey(): void
    {
        $data = [
            'set_at' => '2022-10-20 10:20:30',
            'collected_at' => '2022-10-20 00:10:20',
            'channel' => 'c1',
            'source' => 's1',
            'base' => 'USD',
            'quote' => 'EUR',
            'rate' => 1.5,
        ];

        $item = RateItem::createFromJson($data);

        self::assertSame([], $item->getIntermediate());
    }

    public function testRateCastFromString(): void
    {
        $data = [
            'set_at' => '2022-10-20 10:20:30',
            'collected_at' => '2022-10-20 00:10:20',
            'channel' => 'c1',
            'source' => 's1',
            'base' => 'USD',
            'quote' => 'EUR',
            'rate' => '1.5',
        ];

        $item = RateItem::createFromJson($data);

        self::assertSame(1.5, $item->getRate());
    }
}
