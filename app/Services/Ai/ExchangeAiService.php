<?php

namespace App\Services\Ai;

use App\Services\Ai\Tools\BaseTool;
use App\Services\Ai\Tools\CommissionReportTool;
use App\Services\Ai\Tools\ExchangeTool;
use App\Services\Ai\Tools\ExchangeUserTool;
use App\Services\Ai\Tools\ReferralCodeTool;
use Illuminate\Support\Facades\Http;

class ExchangeAiService
{
    /** @var BaseTool[] */
    protected array $tools = [];

    public function __construct()
    {
        $this->tools = [
            new CommissionReportTool(),
            new ExchangeUserTool(),
            new ReferralCodeTool(),
            new ExchangeTool(),
        ];
    }

    /**
     * Get all JSON Schema definitions
     */
    public function getAllDefinitions(): array
    {
        $definitions = [];
        foreach ($this->tools as $tool) {
            foreach ($tool->definitions() as $definition) {
                // Convert empty properties arrays to JSON objects
                if (isset($definition['function']['parameters']['properties']) && empty($definition['function']['parameters']['properties'])) {
                    $definition['function']['parameters']['properties'] = new \stdClass();
                }
                $definitions[] = $definition;
            }
        }
        return $definitions;
    }

    /**
     * Dispatch tool execution to the corresponding class
     */
    public function dispatchTool(string $name, array $arguments, array $pageFilters = []): mixed
    {
        foreach ($this->tools as $tool) {
            $result = $tool->execute($name, $arguments, $pageFilters);
            if ($result !== null) {
                return $result;
            }
        }
        return ['error' => 'ابزار یافت نشد.'];
    }

    /**
     * Complete communication with OpenAI / proxy
     */
    public function chatWithAi(string $userPrompt, array $pageFilters = []): string
    {
        $apiKey = config('services.ai.api_key');
        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.model');

        if (empty($apiKey)) {
            return "خطا: کلید AI_API_KEY در فایل .env تنظیم نشده است.";
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'تو تحلیلگر هوشمند سامانه رویال‌کلاب هستی. برای پاسخ به سوالات مالی و گزارش‌ها حتماً از ابزارها استفاده کن. پاسخ‌ها فارسی، دقیق و خلاصه باشند. برای خوانایی، هر عنوان و هر مورد فهرست را در خط جداگانه بنویس و بین بخش‌ها یک خط خالی بگذار.'
            ],
            [
                'role' => 'user',
                'content' => $userPrompt
            ]
        ];

        try {
            $response = Http::withToken($apiKey)->timeout(45)->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'tools' => $this->getAllDefinitions(),
                'tool_choice' => 'auto',
            ]);

            if (!$response->successful()) {
                return 'خطا در سرویس هوش مصنوعی: ' . $response->body();
            }

            $message = $response->json('choices.0.message');

            if (!empty($message['tool_calls'])) {
                $messages[] = $message;

                foreach ($message['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name'];
                    $toolArgs = json_decode($toolCall['function']['arguments'], true) ?? [];

                    $result = $this->dispatchTool($toolName, $toolArgs, $pageFilters);

                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE)
                    ];
                }

                $finalResponse = Http::withToken($apiKey)->timeout(45)->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => $messages,
                ]);

                return $finalResponse->json('choices.0.message.content') ?? 'پاسخی دریافت نشد.';
            }

            return $message['content'] ?? 'پاسخی دریافت نشد.';

        } catch (\Throwable $e) {
            return 'خطای سیستمی: ' . $e->getMessage();
        }
    }
    /**
     * Process a predefined question locally or with a single AI request
     */
    public function handlePredefinedQuestion(string $slug, array $pageFilters = []): string
    {
        $question = PredefinedQuestions::find($slug);

        if (!$question) {
            return 'خطا: سوال آماده مورد نظر یافت نشد.';
        }

        // Execute tools directly in Laravel
        $toolOutputs = [];
        foreach ($question['tool_calls'] as $toolCall) {
            $toolName = $toolCall['name'];
            $toolArgs = $toolCall['arguments'] ?? [];
            $toolOutputs[$toolName] = $this->dispatchTool($toolName, $toolArgs, $pageFilters);
        }

        if (($toolOutputs['query_exchange_reports'] ?? null) === []) {
            return $slug === 'top-products-volume'
                ? 'گزارشی با محصول مشخص برای تحلیل یافت نشد.'
                : 'گزارشی برای تحلیل کاربران یافت نشد.';
        }

        // If need_ai is disabled, return an immediate response without AI costs
        if (empty($question['need_ai'])) {
            return PredefinedQuestions::formatLocalAnswer($slug, $toolOutputs);
        }

        // If need_ai is enabled, make one OpenAI request to analyze the extracted data
        return $this->analyzeDataWithAi($question['prompt'] ?? $question['label'], $toolOutputs);
    }

    /**
     * Analyze data in one AI request without invoking tools again
     */
    protected function analyzeDataWithAi(string $prompt, array $toolData): string
    {
        $apiKey = config('services.ai.api_key');
        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.model');

        $messages = [
            [
                'role' => 'system',
                'content' => 'تو تحلیلگر مالی سامانه رویال‌کلاب هستی. داده‌های پایگاه‌داده را به صورت دقیق، روان، خلاصه و به زبان فارسی تحلیل کن. هر عنوان و هر مورد فهرست را در خط جداگانه بنویس و بین بخش‌ها یک خط خالی بگذار.'
            ],
            [
                'role' => 'user',
                'content' => "درخواست: {$prompt}\n\nداده‌های واکشی‌شده:\n" . json_encode($toolData, JSON_UNESCAPED_UNICODE)
            ]
        ];

        try {
            $response = Http::withToken($apiKey)->timeout(45)->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => $messages,
            ]);

            if (!$response->successful()) {
                return 'خطا در ارتباط با هوش مصنوعی: ' . $response->body();
            }

            return $response->json('choices.0.message.content') ?? 'پاسخی دریافت نشد.';
        } catch (\Throwable $e) {
            return 'خطای سیستمی: ' . $e->getMessage();
        }
    }


}
