<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;


class ViewDraftCase extends ViewRecord
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->color('warning'),
            Action::make('openSourceLink')->label('Source')
                ->url(fn () => $this->record->link)->openUrlInNewTab(),
            Actions\DeleteAction::make('approveCase')
                ->label('Approve')
                ->requiresConfirmation()
                ->modalHeading('Approve Case')
                ->modalDescription('Are you sure you\'d like to approve this case?')
                ->modalIcon('heroicon-o-check')

                ->modalSubmitActionLabel('Approve')


                ->before(
                    fn () => $this->record->approve()
                )
                ->color('success')
                ->successNotification(Notification::make()
                    ->success()
                    ->title('Draft Approved')
                    ->body('The Case is approved, and draft is deleted automatically'),)

                ->successRedirectUrl(DraftCaseResource::getUrl('index'))
        ];
    }
}
