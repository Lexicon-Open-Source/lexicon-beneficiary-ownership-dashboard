<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewWork extends ViewRecord
{
    protected static string $resource = WorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->refresh();
                })
                ->hidden(in_array($this->record->status, ['cancelled', 'finished', 'failed'])),
            Action::make('cancel')
                ->label('Cancel Work')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $response = $this->record->cancel();

                    if ($response && $response->successful()) {
                        Notification::make()
                            ->title('Work Cancelled')
                            ->success()
                            ->send();

                        return redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                    }

                    Notification::make()
                        ->title('Failed to Cancel Work')
                        ->body($response?->json('message') ?? 'Unknown error')
                        ->danger()
                        ->send();
                })
                ->hidden(in_array($this->record->status, ['cancelled', 'finished', 'failed'])),
        ];
    }

    public function refresh(): void
    {
        redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
    }
}
