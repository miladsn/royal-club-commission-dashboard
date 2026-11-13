<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionReportResource\Pages;
use App\Models\CommissionReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommissionReportExport;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class CommissionReportResource extends Resource
{
    protected static ?string $model = CommissionReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'گزارش‌های کمیسیون';
    protected static ?string $modelLabel = 'گزارش کمیسیون';
    protected static ?string $pluralModelLabel = 'گزارش‌های کمیسیون و حجم';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('exchange_id')
                    ->relationship('exchange', 'name')
                    ->required()
                    ->label('صرافی / بروکر'),
                Forms\Components\TextInput::make('uid')
                    ->required()
                    ->maxLength(255)
                    ->label('شناسه کاربری (UID)'),
                Forms\Components\TextInput::make('trade_volume')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('حجم معاملات'),
                Forms\Components\TextInput::make('commission')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('کمیسیون'),
                Forms\Components\TextInput::make('referral_code')
                    ->maxLength(255)
                    ->label('کد رفرال'),
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'title')
                    ->nullable()
                    ->label('محصول مرتبط'),
                Forms\Components\Select::make('status')
                    ->options([
                        'matched' => 'تطبیق‌یافته (Matched)',
                        'unmatched' => 'تطبیق‌نیافته (بدون رفرال)',
                        'unknown_uid' => 'UID ناشناخته',
                    ])
                    ->required()
                    ->default('matched')
                    ->label('وضعیت'),
                Forms\Components\DateTimePicker::make('reported_at')
                    ->required()
                    ->default(now())
                    ->label('تاریخ گزارش'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exchange.name')
                    ->sortable()
                    ->searchable()
                    ->label('صرافی')
                    ->url(fn (CommissionReport $record): ?string => $record->exchange && ExchangeResource::canEdit($record->exchange) ? ExchangeResource::getUrl('edit', ['record' => $record->exchange]) : null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('uid')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.exchange-users.index', ['search' => $record->uid]))
                    ->color('primary')
                    ->label('UID کاربر'),
                Tables\Columns\TextColumn::make('trade_volume')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->label('حجم معاملات'),
                Tables\Columns\TextColumn::make('commission')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->label('کمیسیون'),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable()
                    ->label('کد رفرال'),
                Tables\Columns\TextColumn::make('product.title')
                    ->label('محصول مرتبط')
                    ->url(fn (CommissionReport $record): ?string => $record->product && ProductResource::canEdit($record->product) ? ProductResource::getUrl('edit', ['record' => $record->product]) : null)
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'matched',
                        'warning' => 'unmatched',
                        'danger' => 'unknown_uid',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'matched' => 'موفق / تطبیق‌یافته',
                        'unmatched' => 'بدون رفرال',
                        'unknown_uid' => 'UID ناشناخته',
                        default => $state,
                    })
                    ->label('وضعیت'),
                Tables\Columns\TextColumn::make('reported_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاریخ ثبت'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exchange_id')
                    ->relationship('exchange', 'name')
                    ->label('فیلتر بر اساس صرافی'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'matched' => 'موفق / تطبیق‌یافته',
                        'unmatched' => 'بدون رفرال',
                        'unknown_uid' => 'UID ناشناخته',
                    ])
                    ->label('فیلتر وضعیت'),

                Tables\Filters\Filter::make('uids')
                    ->form([
                        Forms\Components\TextInput::make('uids')
                            ->label('گروهی (با کاما جدا کنید) UID فیلتر')
                            ->placeholder('UID_USER_1, UID_USER_2'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $uids = $data['uids'] ?? '';

                        if (blank($uids)) {
                            return $query;
                        }

                        $uids = array_values(array_unique(array_filter(
                            array_map('trim', preg_split('/[,;\r\n]+/', $uids))
                        )));

                        return empty($uids)
                            ? $query
                            : $query->whereIn('uid', $uids);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $uidsStr = trim($data['uids'] ?? '');
                        if ($uidsStr === '') {
                            return null;
                        }

                        $count = count(array_filter(array_map('trim', explode(',', $uidsStr))));

                        return "فیلتر UID ({$count} مورد)";
                    })
                    ->label('UIDها'),


                Tables\Filters\Filter::make('reported_at')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')->label('از تاریخ'),
                        Forms\Components\DatePicker::make('until_date')->label('تا تاریخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('reported_at', '>=', $date),
                            )
                            ->when(
                                $data['until_date'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('reported_at', '<=', $date),
                            );
                    })
                    ->label('بازه زمانی گزارش'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_excel')
                    ->label('خروجی اکسل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('تنظیمات خروجی اکسل')
                    ->modalSubmitActionLabel('دانلود فایل')
                    ->modalCancelActionLabel('انصراف')
                    ->modalWidth('md')                    ->form([
                        Select::make('type')
                            ->label('نوع گزارش خروجی')
                            ->options([
                                'detailed' => 'گزارش کامل',
                                'summary_user' => 'گزارش تجمیعی کاربران (جمع حجم و کمیسیون هر کاربر)',
                            ])
                            ->default('detailed')
                            ->required()
                            ->native(false),

                        Toggle::make('include_total')
                            ->label('نمایش سطر جمع کل در انتهای فایل')
                            ->default(true)
                            ->helperText('مجموع کل حجم معاملات و کمیسیون در انتهای فایل درج خواهد شد.'),
                    ])
                    ->action(function (array $data, $livewire) {
                        // Use the current table filters
                        $filteredQuery = $livewire->getFilteredTableQuery();

                        $fileName = 'royal_report_' . now()->format('Y-m-d_His') . '.xlsx';

                        return Excel::download(
                            new CommissionReportExport(
                                query: $filteredQuery,
                                type: $data['type'],
                                includeTotal: (bool) $data['include_total']
                            ),
                            $fileName
                        );
                    }),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissionReports::route('/'),
            'create' => Pages\CreateCommissionReport::route('/create'),
            'edit' => Pages\EditCommissionReport::route('/{record}/edit'),
        ];
    }
}
