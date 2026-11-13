<?php

namespace App\Services\Ai\Tools;

use App\Models\ExchangeUser;
use App\Models\CommissionReport;
use App\Services\Ai\ReportFilters;

class ExchangeUserTool extends BaseTool
{
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_exchange_users',
                    'description' => 'بررسی لیست و تعداد کاربران ثبت‌شده در صرافی‌ها.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'exchange_slug' => ['type' => 'string', 'description' => 'اسلاگ صرافی']
                        ]
                    ]
                ]
            ]
        ];
    }

    public function execute(string $name, array $arguments, array $pageFilters = []): mixed
    {
        if ($name === 'search_exchange_users') {
            $query = ExchangeUser::with('exchange');
            if (!empty($arguments['exchange_slug'])) {
                $query->whereHas('exchange', fn($q) => $q->where('slug', $arguments['exchange_slug']));
            }
            if ($pageFilters !== []) {
                $reports = ReportFilters::apply(CommissionReport::query(), $pageFilters)
                    ->whereColumn('commission_reports.exchange_id', 'exchange_users.exchange_id')
                    ->whereColumn('commission_reports.uid', 'exchange_users.uid');
                $query->whereExists($reports->selectRaw('1')->toBase());
            }
            return [
                'total_users' => $query->count(),
                'sample_uids' => $query->limit(10)->pluck('uid')->toArray()
            ];
        }
        return null;
    }
}
