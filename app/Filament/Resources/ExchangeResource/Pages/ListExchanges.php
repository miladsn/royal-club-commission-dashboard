<?php

namespace App\Filament\Resources\ExchangeResource\Pages;

use App\Filament\Resources\ExchangeResource;
use App\Models\Exchange;
use App\Services\Exchanges\ExchangeSyncDispatcher;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ListExchanges extends ListRecords
{
    protected static string $resource = ExchangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make()
        ];
    }



}
