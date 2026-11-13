<?php

namespace App\Services\Ai;

use Illuminate\Database\Eloquent\Builder;

class ReportFilters
{
    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['exchange_id'] ?? null, fn (Builder $q, $value) => $q->where('commission_reports.exchange_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $q, $value) => $q->where('commission_reports.status', $value))
            ->when($filters['uids'] ?? null, fn (Builder $q, $value) => $q->whereIn('commission_reports.uid', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $value) => $q->whereDate('commission_reports.reported_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $value) => $q->whereDate('commission_reports.reported_at', '<=', $value));
    }
}
