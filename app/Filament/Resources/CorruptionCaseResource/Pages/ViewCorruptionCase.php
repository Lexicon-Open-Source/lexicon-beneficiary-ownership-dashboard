<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCorruptionCase extends ViewRecord
{
    protected static string $resource = CorruptionCaseResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make()->color('warning'),
            Action::make('openSourceLink')->label('Source')
                ->url(fn () => $this->record->link)->openUrlInNewTab()
        ];
    }
}
