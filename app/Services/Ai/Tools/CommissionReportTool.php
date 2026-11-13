<?php

namespace App\Services\Ai\Tools;

use App\Models\CommissionReport;
use App\Services\Ai\ReportFilters;

class CommissionReportTool extends BaseTool
{
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_commission_reports',
                    'description' => 'جستجو و استخراج گزارش‌های کمیسیون و حجم معاملات با فیلتر صرافی، UID، کد ریفرال و بازه زمانی.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'exchange_slug' => ['type' => 'string', 'description' => 'اسلاگ صرافی مانند lbank, bitunix, orbit'],
                            'uid' => ['type' => 'string', 'description' => 'شناسه UID کاربر'],
                            'referral_code' => ['type' => 'string', 'description' => 'کد ریفرال'],
                            'start_date' => ['type' => 'string', 'description' => 'تاریخ شروع YYYY-MM-DD'],
                            'end_date' => ['type' => 'string', 'description' => 'تاریخ پایان YYYY-MM-DD'],
                            'limit' => ['type' => 'integer', 'description' => 'تعداد نتایج (پیش‌فرض 20)'],
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_commission_summary',
                    'description' => 'مجموع حجم معاملات و کمیسیون‌های تجمیع شده.',
                    'parameters' => ['type' => 'object', 'properties' => []]
                ]
            ]
        ];
    }

    public function execute(string $name, array $arguments, array $pageFilters = []): mixed
    {
        return match ($name) {
            'search_commission_reports' => $this->searchReports($arguments, $pageFilters),
            'get_commission_summary' => $this->getSummary($pageFilters),
            default => null,
        };
    }

    protected function searchReports(array $args, array $pageFilters): array
    {
        $query = ReportFilters::apply(CommissionReport::with(['exchange', 'product']), $pageFilters);

        if (!empty($args['exchange_slug'])) {
            $query->whereHas('exchange', fn($q) => $q->where('slug', $args['exchange_slug']));
        }
        if (!empty($args['uid'])) {
            $query->where('uid', 'like', "%{$args['uid']}%");
        }
        if (!empty($args['referral_code'])) {
            $query->where('referral_code', 'like', "%{$args['referral_code']}%");
        }
        if (!empty($args['start_date'])) {
            $query->whereDate('reported_at', '>=', $args['start_date']);
        }
        if (!empty($args['end_date'])) {
            $query->whereDate('reported_at', '<=', $args['end_date']);
        }

        $reports = $query->latest('reported_at')->limit($args['limit'] ?? 20)->get();

        if ($reports->isEmpty()) {
            return ['message' => 'داده‌ای یافت نشد (داده ناقص/ناموجود).'];
        }

        return $reports->map(fn($r) => [
            'exchange' => $r->exchange?->name,
            'uid' => $r->uid,
            'trade_volume' => $r->trade_volume,
            'commission' => $r->commission,
            'referral_code' => $r->referral_code,
            'product' => $r->product?->title ?? 'نامشخص',
            'reported_at' => $r->reported_at?->toDateTimeString(),
        ])->toArray();
    }

    protected function getSummary(array $pageFilters): array
    {
        return ReportFilters::apply(CommissionReport::query(), $pageFilters)->selectRaw('sum(trade_volume) as total_volume, sum(commission) as total_commission, count(*) as total_count')
            ->first()
            ->toArray();
    }
}
