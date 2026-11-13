<?php

namespace App\Filament\Resources\CommissionReportResource\Pages;

use App\Filament\Resources\CommissionReportResource;
use App\Filament\Resources\CommissionReportResource\Widgets\CommissionAiChatWidget;
use App\Models\Exchange;
use App\Services\Exchanges\ExchangeSyncDispatcher;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListCommissionReports extends ListRecords
{
    protected static string $resource = CommissionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
            Actions\Action::make('syncExchanges')
                ->label('همگام‌سازی صرافی‌ها')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->form([
                    Select::make('exchange_id')
                        ->label('انتخاب صرافی')
                        ->options(Exchange::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('excel_file', null)),

                    // API exchange settings
                   Section::make('تنظیمات همگام‌سازی API')
                        ->schema([
                           TextInput::make('user_count')
                                ->label('تعداد کاربران')
                                ->numeric()
                                ->default(20),
                            TextInput::make('report_count')
                                ->label('تعداد گزارش‌ها')
                                ->numeric()
                                ->default(35),
                           DatePicker::make('start_date')
                                ->label('تاریخ شروع'),
                           DatePicker::make('end_date')
                                ->label('تاریخ پایان'),
                        ])
                        ->visible(function (callable $get) {
                            $exchangeId = $get('exchange_id');
                            if (!$exchangeId) return false;
                            $exchange = Exchange::find($exchangeId);
                            return $exchange && $exchange->sync_method === 'api';
                        }),

                    // Excel file upload section
                  Section::make('بارگذاری فایل اکسل')
                        ->schema([
                          ViewField::make('download_link')
                                ->view('filament.forms.components.excel-download-link'),

                          FileUpload::make('excel_file')
                                ->label('انتخاب فایل اکسل گزارش (.xlsx, .xls)')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                    'text/csv',
                                ])
                                ->directory('temp_excel_uploads')
                                ->required(),
                        ])
                        ->visible(function (callable $get) {
                            $exchangeId = $get('exchange_id');
                            if (!$exchangeId) return false;
                            $exchange = Exchange::find($exchangeId);
                            return $exchange && $exchange->sync_method === 'excel';
                        }),
                ])
                ->action(action: function (array $data, ExchangeSyncDispatcher $dispatcher) {
                    $exchange = Exchange::findOrFail($data['exchange_id']);

                    $options = [
                        'user_count' => $data['user_count'] ?? 20,
                        'report_count' => $data['report_count'] ?? 35,
                        'start_date' => !empty($data['start_date']) ? $data['start_date'] . ' 00:00:00' : null,
                        'end_date' => !empty($data['end_date']) ? $data['end_date'] . ' 23:59:59' : null,
                    ];

                    $filePath = null;
                    if ($exchange->sync_method === 'excel' && !empty($data['excel_file'])) {
                        $filePath = Storage::disk(config('filament.default_filesystem_disk', 'public'))->path($data['excel_file']);

                        if (!file_exists($filePath)) {
                            $filePath = storage_path('app/public/' . $data['excel_file']);
                        }
                    }

                    try {
                        $result = $dispatcher->handle($exchange, $options, $filePath);

                        // Safely handle dispatchers that still return a number
                        if (is_array($result)) {
                            $created = $result['created'] ?? 0;
                            $updated = $result['updated'] ?? 0;

                            $bodyMessage = "صرافی {$exchange->name}: {$created} رکورد جدید ثبت شد.";
                            if ($updated > 0) {
                                $bodyMessage .= " ({$updated} رکورد تکراری شناسایی و به‌روزرسانی شد)";
                            }
                        } else {
                            $bodyMessage = "تعداد {$result} رکورد از صرافی {$exchange->name} ثبت گردید.";
                        }

                        Notification::make()
                            ->title('همگام‌سازی با موفقیت انجام شد')
                            ->body($bodyMessage)
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('خطا در همگام‌سازی')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
    public function getWidgetData(): array
    {
        return ['tableFilters' => $this->tableFilters ?? []];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CommissionAiChatWidget::class,
        ];
    }
}
