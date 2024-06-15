<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorruptionCase extends EditRecord
{
    protected static string $resource = CorruptionCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
