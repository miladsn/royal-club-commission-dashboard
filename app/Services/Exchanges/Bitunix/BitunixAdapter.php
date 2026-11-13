<?php

namespace App\Services\Exchanges\Bitunix;

class BitunixAdapter
{
    public static function transform(array $rawItems): array
    {
        return array_map(function ($item) {
            return [
                'uid' => $item['uid'],
                'trade_volume' => (float)$item['trade_volume'],
                'commission' => (float)$item['commission'],
                'reported_at' => $item['date'],
            ];
        }, $rawItems);
    }
}
