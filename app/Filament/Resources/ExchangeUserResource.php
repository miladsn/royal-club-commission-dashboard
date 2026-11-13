<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExchangeUserResource\Pages;
use App\Models\ExchangeUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ExchangeUserResource extends Resource
{
    protected static ?string $model = ExchangeUser::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'کاربران صرافی';
    protected static ?string $modelLabel = 'کاربر صرافی';
    protected static ?string $pluralModelLabel = 'کاربران صرافی';

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
                    ->label('شناسه کاربر (UID)'),
                Forms\Components\Select::make('referral_code_id')
                    ->relationship('referralCode', 'code')
                    ->nullable()
                    ->label('رفرال کد'),
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
                    ->url(fn (ExchangeUser $record): ?string => $record->exchange && ExchangeResource::canEdit($record->exchange) ? ExchangeResource::getUrl('edit', ['record' => $record->exchange]) : null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('uid')
                    ->searchable()
                    ->sortable()
                    ->label('شناسه کاربر (UID)'),
                Tables\Columns\TextColumn::make('referralCode.code')
                    ->searchable()
                    ->sortable()
                    ->label('رفرال کد')
                    ->url(fn (ExchangeUser $record): ?string => $record->referralCode && ReferralCodeResource::canEdit($record->referralCode) ? ReferralCodeResource::getUrl('edit', ['record' => $record->referralCode]) : null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاریخ ثبت')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('تاریخ بروزرسانی')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('exchange_id')
                    ->relationship('exchange', 'name')
                    ->label('فیلتر بر اساس صرافی'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListExchangeUsers::route('/'),
            'create' => Pages\CreateExchangeUser::route('/create'),
            'edit' => Pages\EditExchangeUser::route('/{record}/edit'),
        ];
    }
}
