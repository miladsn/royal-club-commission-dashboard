<?php

namespace App\Filament\Resources\CommissionReportResource\Pages;

use App\Filament\Resources\CommissionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommissionReport extends EditRecord
{
    protected static string $resource = CommissionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
