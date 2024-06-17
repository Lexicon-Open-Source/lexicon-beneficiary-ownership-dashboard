<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewDraftCase extends ViewRecord
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('openSourceLink')->label('Source')
                ->url(fn () => $this->record->link)->openUrlInNewTab()
        ];
    }
}
