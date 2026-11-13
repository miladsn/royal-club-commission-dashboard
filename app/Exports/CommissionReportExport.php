<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Database\Eloquent\Builder;

class CommissionReportExport implements FromCollection, WithHeadings
{
    protected Builder $query;
    protected string $type;
    protected bool $includeTotal;

    public function __construct(Builder $query, string $type = 'detailed', bool $includeTotal = true)
    {
        // Only relationships defined on the CommissionReport model: exchange and product
        $this->query = $query->with(['exchange', 'product']);
        $this->type = $type;
        $this->includeTotal = $includeTotal;
    }

    public function headings(): array
    {
        if ($this->type === 'summary_user') {
            return ['UID کاربر', 'صرافی', 'جمع حجم معاملات', 'جمع کمیسیون', 'تعداد تراکنش'];
        }

        return ['صرافی', 'UID کاربر', 'حجم معاملات', 'کمیسیون', 'کد رفرال', 'محصول', 'وضعیت', 'تاریخ'];
    }

    public function collection()
    {
        if ($this->type === 'summary_user') {
            $records = (clone $this->query)
                ->selectRaw('uid, exchange_id, SUM(trade_volume) as total_volume, SUM(commission) as total_commission, COUNT(*) as count')
                ->groupBy('uid', 'exchange_id')
                ->get()
                ->map(function ($row) {
                    return [
                        'uid' => $row->uid,
                        'exchange' => $row->exchange?->name ?? '---',
                        'total_volume' => number_format($row->total_volume, 2),
                        'total_commission' => number_format($row->total_commission, 2),
                        'count' => $row->count,
                    ];
                });

            if ($this->includeTotal) {
                $totalVolume = $records->sum(fn ($r) => (float) str_replace(',', '', $r['total_volume']));
                $totalCommission = $records->sum(fn ($r) => (float) str_replace(',', '', $r['total_commission']));

                $records->push([
                    'uid' => 'جمع کل',
                    'exchange' => '-',
                    'total_volume' => number_format($totalVolume, 2),
                    'total_commission' => number_format($totalCommission, 2),
                    'count' => $records->sum('count'),
                ]);
            }

            return $records;
        }

        $records = $this->query->get();

        $rows = $records->map(function ($r) {
            return [
                'exchange' => $r->exchange?->name ?? '---',
                'uid' => $r->uid,
                'trade_volume' => number_format($r->trade_volume, 2),
                'commission' => number_format($r->commission, 2),
                'referral_code' => $r->referral_code ?? '-',
                'product' => $r->product?->title ?? 'نامشخص',
                'status' => match ($r->status) {
                    'matched' => 'موفق / تطبیق‌یافته',
                    'unmatched' => 'بدون رفرال',
                    'unknown_uid' => 'ناشناخته',
                    default => $r->status,
                },
                'created_at' => (string) $r->reported_at,
            ];
        });

        if ($this->includeTotal) {
            $rows->push([
                'exchange' => 'جمع کل',
                'uid' => '-',
                'trade_volume' => number_format($records->sum('trade_volume'), 2),
                'commission' => number_format($records->sum('commission'), 2),
                'referral_code' => '-',
                'product' => '-',
                'status' => '-',
                'created_at' => '-',
            ]);
        }

        return $rows;
    }
}
