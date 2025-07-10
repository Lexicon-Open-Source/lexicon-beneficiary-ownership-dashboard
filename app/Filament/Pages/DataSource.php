<?php

namespace App\Filament\Pages;

use App\Models\DataSource as DataSourceModel;
use App\Services\CrawlerService;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class DataSource extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Crawler Management';

    protected static string $view = 'filament.pages.data-source';

    protected static ?string $slug = 'data-sources';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getTableQuery(): Builder
    {
        return DataSourceModel::query();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('country')
                ->required()
                ->length(2),
            TextInput::make('source_type')
                ->required()
                ->maxLength(255),
            TextInput::make('base_url')
                ->required()
                ->url()
                ->maxLength(255),
            Textarea::make('description')
                ->columnSpanFull(),
            KeyValue::make('config')
                ->columnSpanFull(),
            Toggle::make('is_active')
                ->required(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(CrawlerService $service)
    {
        $data = $this->form->getState();

        try {
            $service->createDataSource($data);

            Notification::make()
                ->title('Saved successfully')
                ->success()
                ->send();

            $this->form->fill();

            return redirect(static::getUrl());
        } catch (ConnectionException | RequestException $e) {
            Notification::make()
                ->title('Service Unavailable')
                ->body('Cannot connect to the crawler service. Please check if the service is running and try again.')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('An unexpected error occurred while creating the data source.')
                ->danger()
                ->send();
        }
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('country')
                ->searchable()
                ->sortable(),
            TextColumn::make('source_type')
                ->searchable()
                ->sortable(),
            IconColumn::make('is_active')
                ->boolean(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->form(fn() => $this->getFormSchema())
                ->action(function (array $data, DataSourceModel $record) {
                    try {
                        app(CrawlerService::class)->updateDataSource($record->id, $data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();
                    } catch (ConnectionException | RequestException $e) {
                        Notification::make()
                            ->title('Service Unavailable')
                            ->body('Cannot connect to the crawler service. Please check if the service is running and try again.')
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('An unexpected error occurred while updating the data source.')
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make()
                ->action(function (DataSourceModel $record) {
                    try {
                        app(CrawlerService::class)->deleteDataSource($record->id);

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    } catch (ConnectionException | RequestException $e) {
                        Notification::make()
                            ->title('Service Unavailable')
                            ->body('Cannot connect to the crawler service. Please check if the service is running and try again.')
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('An unexpected error occurred while deleting the data source.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
