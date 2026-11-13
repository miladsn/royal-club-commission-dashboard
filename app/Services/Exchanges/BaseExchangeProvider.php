<?php

namespace App\Services\Exchanges;

use App\Models\Exchange;
use App\Models\CommissionReport;
use App\Models\ExchangeUser;
use Carbon\Carbon;

abstract class BaseExchangeProvider
{
    protected Exchange $exchange;

    public function __construct(string $exchangeSlug)
    {
        $this->exchange = Exchange::where('slug', $exchangeSlug)->firstOrFail();
    }

    public function saveReports(array $standardizedRows): array
    {
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($standardizedRows as $row) {
            $uid = $row['uid'];

            $exchangeUser = ExchangeUser::where('exchange_id', $this->exchange->id)
                ->where('uid', $uid)
                ->with('referralCode')
                ->first();

            $status = 'unknown_uid';
            $referralCodeString = null;
            $productId = null;

            if ($exchangeUser) {
                $referralCode = $exchangeUser->referralCode;

                if ($referralCode && (string) $referralCode->exchange_id === (string) $this->exchange->id) {
                    $status = 'matched';
                    $referralCodeString = $referralCode->code;
                    $productId = $referralCode->product_id;
                } else {
                    $status = 'unmatched';
                }
            }

            $reportedAt = isset($row['reported_at'])
                ? Carbon::parse($row['reported_at'])
                : now();

            $tradeVolume = $row['trade_volume'] ?? 0;

            $report = CommissionReport::updateOrCreate(
                [
                    'exchange_id'  => $this->exchange->id,
                    'uid'          => $uid,
                    'reported_at'  => $reportedAt,
                    'trade_volume' => $tradeVolume,
                ],
                [
                    'commission'    => $row['commission'] ?? 0,
                    'referral_code' => $referralCodeString,
                    'product_id'    => $productId,
                    'status'        => $status,
                ]
            );

            if ($report->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'total'   => count($standardizedRows),
        ];
    }
}
