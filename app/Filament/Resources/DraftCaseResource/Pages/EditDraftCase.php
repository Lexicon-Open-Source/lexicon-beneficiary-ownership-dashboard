<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use League\CommonMark\CommonMarkConverter;
use Illuminate\Database\Eloquent\Model;

class EditDraftCase extends EditRecord
{
    protected static string $resource = DraftCaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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
