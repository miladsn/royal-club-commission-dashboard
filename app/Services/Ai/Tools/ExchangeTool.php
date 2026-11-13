<?php

namespace App\Services\Ai\Tools;

use App\Models\CommissionReport;
use App\Services\Ai\ReportFilters;
use Illuminate\Database\Eloquent\Builder;

class ExchangeTool extends BaseTool
{
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'query_exchange_reports',
                    'description' => 'جستجو، تحلیل و گروه‌بندی داده‌های کمیسیون و حجم معاملات بر اساس فیلترهای صرافی، محصول، وضعیت و بازه زمانی.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'exchange_id' => ['type' => 'integer', 'description' => 'شناسه صرافی'],
                            'product_id' => ['type' => 'integer', 'description' => 'شناسه محصول'],
                            'status' => ['type' => 'string', 'enum' => ['matched', 'unmatched', 'unknown_uid'], 'description' => 'وضعیت رکورد'],
                            'date_from' => ['type' => 'string', 'description' => 'تاریخ شروع YYYY-MM-DD'],
                            'date_to' => ['type' => 'string', 'description' => 'تاریخ پایان YYYY-MM-DD'],
                            'group_by' => ['type' => 'string', 'enum' => ['exchange', 'product', 'uid'], 'description' => 'نحوه گروه‌بندی خروجی'],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function execute(string $name, array $arguments, array $pageFilters = []): mixed
    {
        if ($name === 'query_exchange_reports') {
            // Apply both the page filters and the tool conditions.
            $filters = array_filter($arguments, fn($v) => $v !== null);
            $query = ReportFilters::apply($this->buildQuery($filters), $pageFilters);

            return match ($filters['group_by'] ?? null) {
                'exchange' => $query->selectRaw('exchange_id, SUM(trade_volume) as total_volume, SUM(commission) as total_commission')
                    ->with('exchange:id,name')->groupBy('exchange_id')->get()->toArray(),
                'product' => $query->whereNotNull('product_id')->selectRaw('product_id, SUM(trade_volume) as total_volume, SUM(commission) as total_commission')
                    ->with('product:id,title')->groupBy('product_id')->get()->toArray(),
                'uid' => $query->selectRaw('uid, SUM(trade_volume) as total_volume, SUM(commission) as total_commission, MAX(reported_at) as last_activity')
                    ->addSelect('exchange_id')->with('exchange:id,name')->groupBy('exchange_id', 'uid')->orderByDesc('total_commission')->orderBy('exchange_id')->orderBy('uid')->limit(20)->get()->toArray(),
                default => [
                    'totals' => $query->selectRaw('COUNT(*) as total_records, SUM(trade_volume) as total_volume, SUM(commission) as total_commission')->first()?->toArray()
                ],
            };
        }
        return null;
    }

    protected function buildQuery(array $f): Builder
    {
        return CommissionReport::query()
            ->when($f['exchange_id'] ?? null, fn($q, $v) => $q->where('exchange_id', $v))
            ->when($f['product_id'] ?? null, fn($q, $v) => $q->where('product_id', $v))
            ->when($f['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($f['date_from'] ?? null, fn($q, $v) => $q->whereDate('reported_at', '>=', $v))
            ->when($f['date_to'] ?? null, fn($q, $v) => $q->whereDate('reported_at', '<=', $v));
    }
}
