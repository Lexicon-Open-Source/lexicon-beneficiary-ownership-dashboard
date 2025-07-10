<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

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

    /**
     * List works with optional filtering
     *
     * @param int|null $page
     * @param int|null $limit
     * @param string|null $status
     * @param string|null $search
     * @return \Illuminate\Http\Client\Response
     */
    public function listWorks(?int $page = null, ?int $limit = null, ?string $status = null, ?string $search = null)
    {
        $query = array_filter([
            'page' => $page,
            'limit' => $limit,
            'status' => $status,
            'q' => $search,
        ]);

        return $this->createRequest()->get('works', $query);
    }

    /**
     * Get a specific work by ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\Client\Response
     */
    public function getWork(string $jobId)
    {
        return $this->createRequest()->get("works/{$jobId}");
    }

    /**
     * Cancel a specific work by ID
     *
     * @param string $jobId
     * @return \Illuminate\Http\Client\Response
     */
    public function cancelWork(string $jobId)
    {
        return $this->createRequest()->post("works/{$jobId}/cancel");
    }
}
