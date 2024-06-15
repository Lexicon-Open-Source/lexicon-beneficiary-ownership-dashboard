<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDraftCase extends EditRecord
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
