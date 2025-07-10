<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkResource\Pages;
use App\Filament\Resources\WorkResource\RelationManagers;
use App\Models\Work;
use App\Services\WorkManagerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Crawler Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
                Forms\Components\DateTimePicker::make('updated_at')->disabled(),
                Forms\Components\DateTimePicker::make('started_at')->disabled(),
                Forms\Components\DateTimePicker::make('finished_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('ID copied to clipboard')
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'started' => 'primary',
                        'finished' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('started_at')
                //     ->dateTime()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('finished_at')
                //     ->dateTime()
                //     ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'started' => 'Started',
                        'finished' => 'Finished',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->requiresConfirmation()
                    ->action(function (Work $record) {
                        $record->cancel();
                    })
                    ->visible(fn(Work $record) => in_array($record->status, ['started'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Job Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Job ID')
                            ->fontFamily('mono')
                            ->copyable(),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn(Work $record): string => match ($record->status) {
                                'started' => 'primary',
                                'finished' => 'success',
                                'failed' => 'danger',
                                'cancelled' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Logs')
                    ->description(function (Work $record) {
                        try {
                            $response = app(WorkManagerService::class)->getWork($record->id);
                            if ($response->successful()) {
                                $data = $response->json('data');
                                $logs = $data['logs'] ?? [];
                                return 'Total logs: ' . count($logs) . ' | Job ID: ' . $record->id;
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to load work details in infolist: ' . $e->getMessage());
                        }
                        return 'Job ID: ' . $record->id;
                    })
                    ->schema([
                        ViewEntry::make('logs')
                            ->view('filament.components.terminal-logs')
                            ->state(function (Work $record) {
                                try {
                                    $response = app(WorkManagerService::class)->getWork($record->id);
                                    if ($response->successful()) {
                                        $data = $response->json('data');
                                        return $data['logs'] ?? [];
                                    }
                                } catch (\Exception $e) {
                                    Log::error('Failed to load work logs in infolist: ' . $e->getMessage());
                                }
                                return [];
                            })
                    ])
                    ->collapsed(false)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorks::route('/'),
            'view' => Pages\ViewWork::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
