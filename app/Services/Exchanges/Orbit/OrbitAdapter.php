<?php

namespace App\Services\Exchanges\Orbit;

class OrbitAdapter
{
    public static function transform(array $rawItems): array
    {
        return array_map(function ($item) {
            return [
                'uid' => $item['uid'],
                'trade_volume' => $item['amount'],
                'commission' => $item['fee'],
                'reported_at' => $item['timestamp'],
            ];
        }, $rawItems);
    }
}
