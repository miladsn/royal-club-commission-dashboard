<?php

namespace Tests\Unit;

use App\Services\Ai\PredefinedQuestions;
use PHPUnit\Framework\TestCase;

class PredefinedQuestionsTest extends TestCase
{
    public function test_local_summary_formats_the_tool_totals(): void
    {
        $answer = PredefinedQuestions::formatLocalAnswer('total-summary', [
            'get_commission_summary' => [
                'total_count' => 3,
                'total_volume' => 1250.5,
                'total_commission' => 25,
            ],
        ]);

        $this->assertStringContainsString('3', $answer);
        $this->assertStringContainsString('1,250.50', $answer);
        $this->assertStringContainsString('25.00', $answer);
    }
}
