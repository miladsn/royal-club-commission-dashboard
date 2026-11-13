<?php

namespace App\Services\Exchanges\Orbit;

use App\Services\Exchanges\BaseExchangeProvider;
use App\Services\Exchanges\Contracts\SyncableExchangeInterface;
use App\Services\FakeExchangePayloadGenerator;

class OrbitProvider extends BaseExchangeProvider implements SyncableExchangeInterface
{
    public function __construct()
    {
        parent::__construct('orbit');
    }

    public function syncData(array $options = []): array
    {
        $generator = new FakeExchangePayloadGenerator();
        $rawResult = $generator->generate(
            'orbit',
            $options['user_count'] ?? 20,
            $options['report_count'] ?? 30,
            $options['start_date'] ?? null,
            $options['end_date'] ?? null
        );

        $rawItems = $rawResult['payload']['data']['records'] ?? [];

        return OrbitAdapter::transform($rawItems);
    }
}
