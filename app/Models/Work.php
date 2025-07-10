<?php

namespace App\Models;

use App\Services\WorkManagerService;
use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Work extends Model
{
    use Sushi;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $schema = [
        'id' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getRows()
    {
        $response = app(WorkManagerService::class)->listWorks();

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json('data', []);

        $works = Arr::map($data, function ($work) {
            return Arr::only($work, ['id', 'status', 'created_at', 'updated_at', 'started_at', 'finished_at']);
        });

        return $works;
    }

    protected function sushiShouldCache()
    {
        return false; // Disable caching to always get fresh data
    }

    public function cancel()
    {
        $response = app(WorkManagerService::class)->cancelWork($this->id);

        if ($response->successful()) {
            $this->status = 'cancelled';
            $this->save();
        }

        return $response;
    }
}
