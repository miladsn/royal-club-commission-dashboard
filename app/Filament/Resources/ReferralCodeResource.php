<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralCodeResource\Pages;
use App\Filament\Resources\ReferralCodeResource\RelationManagers;
use App\Models\ReferralCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReferralCodeResource extends Resource
{
    protected static ?string $model = ReferralCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'رفرال کدها';
    protected static ?string $modelLabel = 'رفرال کد';
    protected static ?string $pluralModelLabel = 'رفرال کدها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('exchange_id')
                    ->label('صرافی / بروکر')
                    ->relationship('exchange', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label('محصول')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('code')
                    ->label('رفرال کد')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exchange.name')
                    ->label('صرافی / بروکر')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ReferralCode $record): ?string => $record->exchange && ExchangeResource::canEdit($record->exchange) ? ExchangeResource::getUrl('edit', ['record' => $record->exchange]) : null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('product.title')
                    ->label('محصول')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ReferralCode $record): ?string => $record->product && ProductResource::canEdit($record->product) ? ProductResource::getUrl('edit', ['record' => $record->product]) : null)
                    ->color('primary'),
                Tables\Columns\TextColumn::make('code')
                    ->label('رفرال کد')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاریخ ویرایش')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListReferralCodes::route('/'),
            'create' => Pages\CreateReferralCode::route('/create'),
            'edit' => Pages\EditReferralCode::route('/{record}/edit'),
        ];
    }
}
