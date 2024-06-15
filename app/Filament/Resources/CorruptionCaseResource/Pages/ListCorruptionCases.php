<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use App\Models\CorruptionCase;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListCorruptionCases extends ListRecords
{
    protected static string $resource = CorruptionCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Types')->badge(CorruptionCase::query()->count()),
            'blacklist' => Tab::make('Blacklist')
                ->badge(CorruptionCase::query()->where('type', 'blacklist')->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'blacklist')),
            'sanction' => Tab::make('Sanction')
                ->badge(CorruptionCase::query()->where('type', 'sanction')->count())

                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'sanction')),
            'verdict' => Tab::make('Verdict / Judgement')
                ->badge(CorruptionCase::query()->where('type', 'verdict')->count())

                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'verdict')),
        ];
    }
}
