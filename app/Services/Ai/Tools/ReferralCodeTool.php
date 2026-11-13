<?php

namespace App\Services\Ai\Tools;

use App\Models\ReferralCode;

class ReferralCodeTool extends BaseTool
{
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_referral_codes',
                    'description' => 'لیست کدهای ریفرال ثبت‌شده و محصول مرتبط با آن‌ها.',
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

    public function execute(string $name, array $arguments): mixed
    {
        if ($name === 'search_referral_codes') {
            $query = ReferralCode::with(['exchange', 'product']);
            if (!empty($arguments['exchange_slug'])) {
                $query->whereHas('exchange', fn($q) => $q->where('slug', $arguments['exchange_slug']));
            }
            return $query->get()->map(fn($r) => [
                'code' => $r->code,
                'exchange' => $r->exchange?->name,
                'product' => $r->product?->title,
            ])->toArray();
        }
        return null;
    }
}
