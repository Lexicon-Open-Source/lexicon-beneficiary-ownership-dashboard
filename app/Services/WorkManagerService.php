<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkManagerService
{
    private function createRequest(): PendingRequest
    {
        $accessTime = time();
        $apiKey = config('crawler.api_key');
        $salt = config('crawler.salt');
        $signature = hash('sha256', $salt . $accessTime . $apiKey);
        $user = config('crawler.user');
        $identity = config('crawler.identity');

        return Http::baseUrl(config('crawler.base_url') . '/v1/')
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

    /**
     * List works with optional filtering
     *
     * @param int|null $page
     * @param int|null $limit
     * @param string|null $status
     * @param string|null $search
     * @return \Illuminate\Http\Client\Response|null
     */
    public function listWorks(?int $page = null, ?int $limit = null, ?string $status = null, ?string $search = null)
    {
        $query = array_filter([
            'page' => $page,
            'limit' => $limit,
            'status' => $status,
            'q' => $search,
        ]);

        try {
            return $this->createRequest()->get('works', $query);
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to connect to crawler service when listing works', [
                'error' => $e->getMessage(),
                'url' => config('crawler.base_url') . '/works',
            ]);
            return null;
        }
    }

    /**
     * Get a specific work by ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\Client\Response|null
     */
    public function getWork(string $jobId)
    {
        try {
            return $this->createRequest()->get("works/{$jobId}");
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to connect to crawler service when getting work', [
                'error' => $e->getMessage(),
                'job_id' => $jobId,
                'url' => config('crawler.base_url') . "/works/{$jobId}",
            ]);
            return null;
        }
    }

    /**
     * Cancel a specific work by ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\Client\Response|null
     */
    public function cancelWork(string $jobId)
    {
        try {
            return $this->createRequest()->post("works/{$jobId}/cancel");
        } catch (ConnectionException | RequestException $e) {
            Log::error('Failed to connect to crawler service when cancelling work', [
                'error' => $e->getMessage(),
                'job_id' => $jobId,
                'url' => config('crawler.base_url') . "/works/{$jobId}/cancel",
            ]);
            return null;
        }
    }

    /**
     * Check if the crawler service is available
     *
     * @return bool
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = Http::baseUrl(config('crawler.base_url'))->timeout(5)->get('/health');

            return $response->successful();
        } catch (ConnectionException | RequestException $e) {
            return false;
        }
    }
}
