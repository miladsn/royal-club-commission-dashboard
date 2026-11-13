<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class FakeExchangePayloadGenerator
{
    private const EXCHANGES = ['lbank', 'orbit', 'bitunix'];

    public function generate(
        string $exchangeSlug,
        int $userCount = 20,
        int $reportCount = 35,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $exchange = strtolower(trim($exchangeSlug));

        if (!in_array($exchange, self::EXCHANGES)) {
            throw new InvalidArgumentException("Invalid exchange slug: {$exchange}");
        }

        $start = $startDate ? Carbon::parse($startDate) : now()->subDays(30);
        $end = $endDate ? Carbon::parse($endDate) : now();

        $uids = $this->generateUids($exchange, $userCount);
        $reports = [];

        for ($i = 0; $i < $reportCount; $i++) {
            $uid = count($uids) > 0 ? $uids[$i % $userCount] : 'UNKNOWN_UID_' . ($i + 1);

            // Random date in the [start, end] range, capped at today
            $minTimestamp = $start->timestamp;
            $maxTimestamp = min($end->timestamp, now()->timestamp);
            $randomTimestamp = rand($minTimestamp, $maxTimestamp);
            $randomDate = Carbon::createFromTimestamp($randomTimestamp);

            $volume = rand(100, 50000) + (rand(0, 99) / 100);
            $commission = round($volume * 0.002, 2);

            if ($exchange === 'lbank') {
                $reports[] = [
                    'account_uid' => $uid,
                    'volume_amount' => $volume,
                    'commission_earned' => $commission,
                    'report_date' => $randomDate->toDateTimeString(),
                ];
            } elseif ($exchange === 'orbit') {
                $reports[] = [
                    'uid' => $uid,
                    'amount' => $volume,
                    'fee' => $commission,
                    'timestamp' => $randomDate->toDateTimeString(),
                ];
            } elseif ($exchange === 'bitunix') {
                $reports[] = [
                    'uid' => $uid,
                    'trade_volume' => $volume,
                    'commission' => $commission,
                    'date' => $randomDate->toDateTimeString(),
                ];
            }
        }

        if ($exchange === 'orbit') {
            $payload = ['data' => ['records' => $reports]];
        } elseif ($exchange === 'bitunix') {
            $payload = ['commissions' => $reports];
        } else {
            $payload = ['items' => $reports];
        }

        return [
            'exchange' => $exchange,
            'meta' => [
                'user_count' => $userCount,
                'report_count' => $reportCount,
                'start_date' => $start->toDateTimeString(),
                'end_date' => $end->toDateTimeString(),
            ],
            'payload' => $payload,
        ];
    }

    private function getDefaultReferralCode(string $exchange): string
    {
        if ($exchange === 'lbank') {
            return 'ROYAL_LBANK_01';
        }

        if ($exchange === 'bitunix') {
            return 'ROYAL_BIT_02';
        }

        return 'ROYAL_ORBIT_01';
    }

    private function generateUids(string $exchange, int $count): array
    {
        $uids = [];

        if ($exchange === 'lbank') {
            $prefix = 'LB_USER_';
        } elseif ($exchange === 'bitunix') {
            $prefix = 'BX_USER_';
        } else {
            $prefix = 'OR_USER_';
        }

        for ($i = 1; $i <= $count; $i++) {
            $uids[] = $prefix . $i;
        }

        return $uids;
    }
}
