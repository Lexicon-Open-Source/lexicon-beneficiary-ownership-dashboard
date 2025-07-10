<?php

return [
  /*
    |--------------------------------------------------------------------------
    | Crawler Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the crawler microservice.
    | You can find the source code here: https://github.com/lexiconthemes/crawler
    |
    */

  'base_url' => env('CRAWLER_BASE_URL'),

  'timeout' => env('CRAWLER_TIMEOUT', 30),

  'api_key' => env('CRAWLER_API_KEY'),
  'salt' => env('CRAWLER_SALT'),
  'user' => env('CRAWLER_USER', 'developer'),
  'identity' => env('CRAWLER_IDENTITY', 'bo.lexicon.id'),
];
