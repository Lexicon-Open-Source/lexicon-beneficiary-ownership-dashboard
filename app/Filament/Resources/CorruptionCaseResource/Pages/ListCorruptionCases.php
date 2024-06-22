<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use App\Models\CorruptionCase;
use App\Models\Enums\CaseType;
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
                ->badge(CorruptionCase::query()->where('case_type', CaseType::CASE_TYPE_BLACKLIST)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->where('case_type', CaseType::CASE_TYPE_BLACKLIST)),
            'sanction' => Tab::make('Sanction')
                ->badge(CorruptionCase::query()->where('case_type', CaseType::CASE_TYPE_SANCTION)->count())

                ->modifyQueryUsing(fn (Builder $query) => $query->where('case_type', CaseType::CASE_TYPE_SANCTION)),
            'verdict' => Tab::make('Verdict / Judgement')
                ->badge(CorruptionCase::query()->where('case_type', CaseType::CASE_TYPE_VERDICT)->count())

                ->modifyQueryUsing(fn (Builder $query) => $query->where('case_type', CaseType::CASE_TYPE_VERDICT)),
        ];
    }
}
