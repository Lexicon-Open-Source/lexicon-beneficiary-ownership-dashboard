<?php

namespace App\Models;

use App\Services\CrawlerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Sushi\Sushi;

class DataSource extends Model
{
    use Sushi;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'name' => 'string',
        'country' => 'string',
        'source_type' => 'string',
        'base_url' => 'string',
        'description' => 'string',
        'config' => 'string',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'country',
        'source_type',
        'base_url',
        'description',
        'config',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function getRows()
    {
        $response = app(CrawlerService::class)->getDataSources();

        $data = $response['data'] ?? [];

        $datasources = Arr::map($data, function ($dataSource) {
            if (isset($dataSource['config']) && is_array($dataSource['config'])) {
                $dataSource['config'] = json_encode($dataSource['config']);
            }

            return Arr::only($dataSource, ['id', 'name', 'country', 'source_type', 'base_url', 'description', 'config', 'is_active']);
        });

        return $datasources;
    }

    protected function sushiShouldCache()
    {
        return false;
    }
}
