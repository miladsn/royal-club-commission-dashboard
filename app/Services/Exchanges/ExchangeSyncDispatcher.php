<?php

namespace App\Services\Exchanges;

use App\Models\Exchange;
use App\Services\Exchanges\Bitunix\BitunixProvider;
use App\Services\Exchanges\LBank\LBankProvider;
use App\Services\Exchanges\Orbit\OrbitProvider;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ExchangeSyncDispatcher
{
    public function handle(Exchange $exchange, array $options = [], ?string $filePath = null): array
    {
        $provider = $this->resolveProvider($exchange->slug);

        $rows = match ($exchange->sync_method) {
            'api' => $this->runApiSync($provider, $options),
            'excel' => $this->runExcelSync($provider, $filePath, $options),
            default => throw new InvalidArgumentException("روش همگام‌سازی برای صرافی {$exchange->name} معتبر نیست."),
        };

        return $provider->saveReports($rows);
    }

    protected function resolveProvider(string $slug): BaseExchangeProvider
    {
        return match ($slug) {
            'lbank' => new LBankProvider(),
            'orbit' => new OrbitProvider(),
            'bitunix' => new BitunixProvider(),
            default => throw new InvalidArgumentException("پروایدر نامعتبر برای صرافی: {$slug}"),
        };
    }

    protected function runApiSync(BaseExchangeProvider $provider, array $options): array
    {
        if (!method_exists($provider, 'syncData')) {
            throw new InvalidArgumentException('این صرافی از روش API پشتیبانی نمی‌کند.');
        }

        return $provider->syncData($options);
    }

    protected function runExcelSync(BaseExchangeProvider $provider, ?string $filePath, array $options): array
    {
        if (!method_exists($provider, 'parseExcel')) {
            throw new InvalidArgumentException('این صرافی از روش اکسل پشتیبانی نمی‌کند.');
        }

        if (!$filePath) {
            throw new InvalidArgumentException('فایل اکسل برای پردازش ارسال نشده است.');
        }

        return $provider->parseExcel($filePath, $options);
    }
}
