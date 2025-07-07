<?php

namespace App\Filament\Pages;

use App\Models\DataSource;
use App\Services\CrawlerService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;

class Scraper extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = 'Crawler Management';

    protected static string $view = 'filament.pages.scraper';

    protected static ?string $slug = 'scraper';

    #[Locked]
    public array $dataSources = [];

    public ?array $data = [];

    public function mount(): void
    {
        $this->dataSources = DataSource::all()->pluck('name', 'name')->toArray();

        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('data_source')
                    ->options($this->dataSources)
                    ->required(),
                Select::make('action')
                    ->options([
                        'scrape:all' => 'Scrape All',
                        'scrape:by_id' => 'Scrape by ID',
                    ])
                    ->live()
                    ->required(),
                TextInput::make('url_frontier_id')
                    ->visible(fn($get) => $get('action') === 'scrape:by_id')
                    ->required(fn($get) => $get('action') === 'scrape:by_id'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('run')
                ->label('Run Scraper')
                ->submit('run'),
        ];
    }

    public function run(CrawlerService $service): void
    {
        $data = $this->form->getState();

        $service->runScraper($data);

        Notification::make()
            ->title('Scraper job started successfully')
            ->success()
            ->send();

        $this->form->fill();
    }
}
