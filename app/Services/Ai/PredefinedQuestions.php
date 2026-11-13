<?php

namespace App\Services\Ai;

class PredefinedQuestions
{
    /**
     * Get all predefined questions with their icons and styles
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'total-summary',
                'label' => 'مجموع کل کمیسیون‌ها و حجم معاملات',
                'icon' => 'heroicon-m-chart-bar-square',
                'badge_text' => 'محاسبه سریع',
                'badge_class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                'need_ai' => false,
                'tool_calls' => [
                    [
                        'name' => 'get_commission_summary',
                        'arguments' => []
                    ]
                ]
            ],
            [
                'slug' => 'top-users-commission',
                'label' => 'کدام کاربران بیشترین کمیسیون را ایجاد کرده‌اند؟',
                'icon' => 'heroicon-m-trophy',
                'badge_text' => 'تحلیل AI',
                'badge_class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                'need_ai' => true,
                'prompt' => 'بر اساس دیتای گزارش‌های زیر، کاربرانی که بیشترین کمیسیون را ایجاد کرده‌اند تحلیل کن و به همراه مقایسه کوتاه بنویس.',
                'tool_calls' => [
                    [
                        'name' => 'query_exchange_reports',
                        'arguments' => [
                            'group_by' => 'uid'
                        ]
                    ]
                ]
            ],
            [
                'slug' => 'top-products-volume',
                'label' => 'کدام محصول بیشترین حجم معاملات را داشته؟',
                'icon' => 'heroicon-m-cube',
                'badge_text' => 'تحلیل AI',
                'badge_class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
                'need_ai' => true,
                'prompt' => 'بر اساس دیتای دریافتی، محصولات دارای شناسه مشخص را بر اساس حجم معاملات مقایسه کن و پرحجم‌ترین محصول را اعلام کن. گزارش‌های فاقد محصول در این مقایسه لحاظ نشده‌اند.',
                'tool_calls' => [
                    [
                        'name' => 'query_exchange_reports',
                        'arguments' => [
                            'group_by' => 'product'
                        ]
                    ]
                ]
            ],
            [
                'slug' => 'exchange-sync-status',
                'label' => 'آمار کاربران ثبت‌شده صرافی‌ها',
                'icon' => 'heroicon-m-users',
                'badge_text' => 'محاسبه سریع',
                'badge_class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                'need_ai' => false,
                'tool_calls' => [
                    [
                        'name' => 'search_exchange_users',
                        'arguments' => []
                    ]
                ]
            ],
        ];
    }

    /**
     * Find a question by slug
     */
    public static function find(string $slug): ?array
    {
        return collect(self::all())->firstWhere('slug', $slug);
    }

    /**
     * Format a direct, fast response for questions that do not require AI
     */
    public static function formatLocalAnswer(string $slug, array $toolData): string
    {
        if ($slug === 'total-summary') {
            $summary = $toolData['get_commission_summary'] ?? [];
            $volume = number_format((float) ($summary['total_volume'] ?? 0), 2);
            $commission = number_format((float) ($summary['total_commission'] ?? 0), 2);
            $count = number_format((int) ($summary['total_count'] ?? 0));

            return "📊 **خلاصه کلی عملکرد سامانه:**\n" .
                "- **تعداد کل رکوردها:** {$count}\n" .
                "- **مجموع حجم معاملات:** {$volume}\n" .
                "- **مجموع کمیسیون پرداختی:** {$commission}";
        }

        if ($slug === 'exchange-sync-status') {
            $users = $toolData['search_exchange_users'] ?? [];
            $total = number_format((int) ($users['total_users'] ?? 0));
            return "👥 **آمار حساب‌های صرافی:**\n- تعداد حساب‌های ثبت‌شده: {$total} حساب.\n- تعداد افراد یکتا مشخص نیست؛ شناسه مشترک میان حساب‌های صرافی‌های مختلف ثبت نشده است.";
        }

        return json_encode($toolData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
