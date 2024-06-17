<?php

namespace App\Filament\Resources\CorruptionCaseResource\Pages;

use App\Filament\Resources\CorruptionCaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\Uid\Ulid;

class CreateCorruptionCase extends CreateRecord
{
    protected static string $resource = CorruptionCaseResource::class;


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
