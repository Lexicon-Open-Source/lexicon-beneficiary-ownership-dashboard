<?php

namespace App\Filament\Resources\DraftCaseResource\Pages;

use App\Filament\Resources\DraftCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Uid\Ulid;

class CreateDraftCase extends CreateRecord
{
    protected static string $resource = DraftCaseResource::class;


    protected function handleRecordCreation(array $data): Model
    {
        $id = Ulid::generate();
        $data['id'] = $id;
        $converter = new CommonMarkConverter();

        $formatted = $data['summary_formatted'];

        $converted = $converter->convert($formatted);
        $summary = strip_tags($converted->getContent());

        $data['summary'] = $summary;

        return static::getModel()::create($data);
    }
}
