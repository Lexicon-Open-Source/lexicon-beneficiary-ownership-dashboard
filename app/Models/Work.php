<?php

namespace App\Models;

use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Work extends Model
{
  use Sushi;

  protected $schema = [
    'id' => 'string',
    'name' => 'string',
    'status' => 'string',
    'created_at' => 'datetime',
    'payload' => 'string',
    'exception' => 'string',
  ];

  public function getRows()
  {
    $baseUrl = config('crawler.base_url');
    if (! $baseUrl) {
      return [];
    }

    $response = Http::get("{$baseUrl}/works", [
      'limit' => 1000,
    ]);

    if ($response->failed()) {
      return [];
    }

    return $response->json();
  }
}
