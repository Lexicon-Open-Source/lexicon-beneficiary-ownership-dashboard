<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Database\Eloquent\Model;

class EditCorruptionCase extends EditRecord
{
    protected static string $resource = CorruptionCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $converter = new CommonMarkConverter();
        $formatted = $data['summary_formatted'];
        $converted = $converter->convert($formatted);
        $summary = strip_tags($converted->getContent());

        $data['summary'] = $summary;

        $record->update($data);

        return $record;
    }
}
