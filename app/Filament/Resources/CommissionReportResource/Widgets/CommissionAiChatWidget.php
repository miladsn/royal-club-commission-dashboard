<?php

namespace App\Filament\Resources\CommissionReportResource\Widgets;

use Filament\Widgets\Widget;
use Livewire\Attributes\Reactive;
use App\Services\Ai\PredefinedQuestions;

class CommissionAiChatWidget extends Widget
{
    #[Reactive]
    public array $tableFilters = [];

    protected static string $view = 'filament.resources.commission-report-resource.widgets.commission-ai-chat-widget';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'predefinedQuestions' => PredefinedQuestions::all(),
        ];
    }
}
