<?php

namespace App\Services\Exchanges\LBank;

use App\Services\Exchanges\BaseExchangeProvider;
use App\Services\Exchanges\Contracts\SyncableExchangeInterface;
use App\Services\FakeExchangePayloadGenerator;

class LBankProvider extends BaseExchangeProvider implements SyncableExchangeInterface
{
    public function __construct()
    {
        parent::__construct('lbank');
    }

    public function syncData(array $options = []): array
    {
        $generator = new FakeExchangePayloadGenerator();
        $rawResult = $generator->generate(
            'lbank',
            $options['user_count'] ?? 20,
            $options['report_count'] ?? 35,
            $options['start_date'] ?? null,
            $options['end_date'] ?? null
        );

        $rawItems = $rawResult['payload']['items'] ?? [];

        return LBankAdapter::transform($rawItems);
    }
}
