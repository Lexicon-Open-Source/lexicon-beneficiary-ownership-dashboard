<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDraftCase extends ViewRecord
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
