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
}
