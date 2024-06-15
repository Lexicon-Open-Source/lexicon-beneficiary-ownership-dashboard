<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDraftCases extends ListRecords
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
