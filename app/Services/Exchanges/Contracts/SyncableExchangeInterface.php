<?php

namespace App\Services\Exchanges\Contracts;

interface SyncableExchangeInterface
{
    public function syncData(array $options = []): array;
}
