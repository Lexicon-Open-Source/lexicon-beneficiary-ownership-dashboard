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

class Crawler extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationGroup = 'Crawler Management';

    protected static string $view = 'filament.pages.crawler';

    protected static ?string $slug = 'crawler';

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
                        'crawl:all' => 'Crawl All',
                        'crawl:by_keyword' => 'Crawl by Keyword',
                        'crawl:by_url' => 'Crawl by URL',
                    ])
                    ->live()
                    ->required(),
                TextInput::make('keyword')
                    ->visible(fn($get) => $get('action') === 'crawl:by_keyword')
                    ->required(fn($get) => $get('action') === 'crawl:by_keyword'),
                TextInput::make('url')
                    ->url()
                    ->visible(fn($get) => $get('action') === 'crawl:by_url')
                    ->required(fn($get) => $get('action') === 'crawl:by_url'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('run')
                ->label('Run Crawler')
                ->submit('run'),
        ];
    }

    public function run(CrawlerService $service): void
    {
        $data = $this->form->getState();

        $service->runCrawler($data);

        Notification::make()
            ->title('Crawler job started successfully')
            ->success()
            ->send();

        $this->form->fill();
    }
}
