<?php

namespace App\Filament\Resources\CommissionReportResource\Pages;

use App\Filament\Resources\CommissionReportResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\Exchange;

class BulkReportCommission extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = CommissionReportResource::class;

    protected static string $view = 'filament.resources.commission-report-resource.pages.bulk-report-commission';

    protected static ?string $title = 'گزارش‌گیری گروهی UIDها';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('exchange_id')
                    ->label('صرافی / بروکر')
                    ->options(Exchange::pluck('name', 'id'))
                    ->required(),

                Forms\Components\Textarea::make('uids')
                    ->label('لیست UIDها (هر UID در یک خط یا جدا شده با کاما)')
                    ->required()
                    ->rows(5)
                    ->placeholder("UID_001\nUID_002\nUID_003"),

                Forms\Components\DatePicker::make('start_date')
                    ->label('تاریخ شروع گزارش')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('تاریخ پایان گزارش')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $formData = $this->form->getState();

        $uidsRaw = $formData['uids'];

        $uids = collect(preg_split('/[\r\n,]+/', $uidsRaw))
            ->map(fn ($uid) => trim($uid))
            ->filter()
            ->unique()
            ->toArray();

        Notification::make()
            ->title('گزارش گروهی با موفقیت دریافت شد')
            ->body('تعداد ' . count($uids) . ' شناسه بررسی شد.')
            ->success()
            ->send();
    }
}
