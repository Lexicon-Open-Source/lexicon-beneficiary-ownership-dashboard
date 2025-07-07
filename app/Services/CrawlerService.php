<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrawlerService
{
    private function createRequest(): PendingRequest
    {
        $accessTime = time();
        $apiKey = config('crawler.api_key');
        $salt = config('crawler.salt');
        $signature = hash('sha256', $salt . $accessTime . $apiKey);
        $user = config('crawler.user');
        $identity = config('crawler.identity');

        return Http::baseUrl(config('crawler.base_url'))
            ->timeout(config('crawler.timeout'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'X-ACCESS-TIME' => $accessTime,
                'X-API-KEY' => $apiKey,
                'X-REQUEST-SIGNATURE' => $signature,
                'X-API-USER' => $user,
                'X-REQUEST-IDENTITY' => $identity,
            ]);
    }

    public function getHealth(): array
    {
        try {
            return $this->createRequest()->get('/health')->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to get crawler health', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getDataSources(array $query = []): array
    {
        try {
            $response = $this->createRequest()->get('/datasources', $query);

            if ($response->failed()) {
                Log::error('Crawler service returned an error when fetching data sources.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();
            Log::info('Successfully fetched data sources from crawler service.', [
                'response' => $data,
            ]);

            return $data ?? [];
        } catch (ConnectionException | RequestException $e) {
            Log::error('Request to crawler service failed when fetching data sources.', [
                'error' => $e->getMessage(),
                'url' => config('crawler.base_url') . '/datasources',
            ]);
            return [];
        }
    }

    public function createDataSource(array $data): array
    {
        try {
            return $this->createRequest()->post('/datasources', $data)->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to create data source', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getDataSource(string $id): array
    {
        try {
            return $this->createRequest()->get("/datasources/{$id}")->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to get data source', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateDataSource(string $id, array $data): array
    {
        try {
            return $this->createRequest()->put("/datasources/{$id}", $data)->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to update data source', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteDataSource(string $id): void
    {
        try {
            $this->createRequest()->delete("/datasources/{$id}");
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to delete data source', ['id' => $id, 'error' => $e->getMessage()]);
        }
    }

    public function runCrawler(array $data): array
    {
        try {
            return $this->createRequest()->post('/crawlers', $data)->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to run crawler', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function runScraper(array $data): array
    {
        try {
            return $this->createRequest()->post('/scrapers', $data)->json();
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to run scraper', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
