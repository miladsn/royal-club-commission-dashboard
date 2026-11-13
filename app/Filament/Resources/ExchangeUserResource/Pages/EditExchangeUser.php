<?php

namespace App\Filament\Resources\ExchangeUserResource\Pages;

use App\Filament\Resources\ExchangeUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExchangeUser extends EditRecord
{
    protected static string $resource = ExchangeUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
