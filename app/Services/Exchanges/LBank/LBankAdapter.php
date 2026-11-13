<?php

namespace App\Services\Exchanges\LBank;

class LBankAdapter
{
    public static function transform(array $rawItems): array
    {
        return array_map(function ($item) {
            return [
                'uid' => $item['account_uid'],
                'trade_volume' => $item['volume_amount'],
                'commission' => $item['commission_earned'],
                'reported_at' => $item['report_date'],
            ];
        }, $rawItems);
    }
}
