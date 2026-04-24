<?php
/**
 * Apify API Service
 * 
 * Handles communication with Apify API for:
 * - Starting actor runs
 * - Checking run status
 * - Fetching dataset items
 * 
 * Uses the scraply/x-twitter-posts-search actor
 */
class ApifyService
{
    private string $apiToken;
    private string $actorId;
    private string $baseUrl;
    private int $maxRetries;
    private int $retryDelay;
    private int $pollInterval;

    public function __construct()
    {
        $this->apiToken = getSetting('apify_api_token', APIFY_API_TOKEN);
        $this->actorId = getSetting('apify_actor_id', APIFY_ACTOR_ID);
        $this->baseUrl = APIFY_BASE_URL;
        $this->maxRetries = APIFY_MAX_RETRIES;
        $this->retryDelay = APIFY_RETRY_DELAY;
        $this->pollInterval = APIFY_POLL_INTERVAL;
    }

    /**
     * Start an actor run with given input
     */
    public function startRun(array $input): array
    {
        $url = "{$this->baseUrl}/acts/{$this->actorId}/runs?token={$this->apiToken}";
        
        $response = $this->makeRequest('POST', $url, $input);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => 'Failed to start Apify run: ' . ($response['error'] ?? 'Unknown error'),
                'run_id' => null
            ];
        }

        $data = $response['data'];
        return [
            'success' => true,
            'run_id' => $data['id'] ?? null,
            'status' => $data['status'] ?? 'UNKNOWN',
            'data' => $data
        ];
    }

    /**
     * Build Apify input from project targets
     */
    public function buildInput(array $targets, array $options = []): array
    {
        $startUrls = [];
        foreach ($targets as $target) {
            $target = trim($target);
            if (empty($target)) continue;

            // Detect target type
            if (preg_match('/^https?:\/\//', $target)) {
                $startUrls[] = $target;
            } elseif (preg_match('/^@/', $target)) {
                $startUrls[] = $target;
            } elseif (preg_match('/^#/', $target)) {
                $startUrls[] = 'search: ' . $target;
            } else {
                $startUrls[] = $target;
            }
        }

        $maxTweets = $options['max_tweets'] ?? (int) getSetting('apify_max_tweets', APIFY_DEFAULT_MAX_TWEETS);
        $timeWindow = $options['time_window'] ?? (int) getSetting('apify_time_window', APIFY_DEFAULT_TIME_WINDOW);
        $searchType = $options['search_type'] ?? getSetting('apify_search_type', APIFY_DEFAULT_SEARCH_TYPE);
        $useProxy = (bool) getSetting('apify_use_proxy', 1);

        $input = [
            'startUrls' => $startUrls,
            'maxTweets' => $maxTweets,
            'searchType' => $searchType
        ];

        if ($timeWindow > 0 && $searchType === 'latest') {
            $input['timeWindow'] = $timeWindow;
        }

        if ($useProxy) {
            $input['proxyConfiguration'] = [
                'useApifyProxy' => true
            ];
        }

        return $input;
    }

    /**
     * Get run status
     */
    public function getRunStatus(string $runId): array
    {
        $url = "{$this->baseUrl}/actor-runs/{$runId}?token={$this->apiToken}";
        
        $response = $this->makeRequest('GET', $url);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to get run status',
                'status' => 'UNKNOWN'
            ];
        }

        $data = $response['data'];
        return [
            'success' => true,
            'run_id' => $data['id'] ?? $runId,
            'status' => $data['status'] ?? 'UNKNOWN',
            'stats' => $data['stats'] ?? [],
            'data' => $data
        ];
    }

    /**
     * Wait for run to complete with polling
     */
    public function waitForRun(string $runId, int $timeout = 300): array
    {
        $startTime = time();
        $terminalStatuses = ['SUCCEEDED', 'FAILED', 'ABORTED', 'TIMED-OUT'];

        while (time() - $startTime < $timeout) {
            $status = $this->getRunStatus($runId);
            
            if (!$status['success']) {
                return $status;
            }

            if (in_array($status['status'], $terminalStatuses)) {
                return $status;
            }

            sleep($this->pollInterval);
        }

        return [
            'success' => false,
            'error' => 'Run timed out waiting for completion',
            'status' => 'TIMEOUT'
        ];
    }

    /**
     * Fetch dataset items from a completed run
     */
    public function fetchDatasetItems(string $runId, int $limit = 1000, int $offset = 0): array
    {
        // First get the dataset ID from the run
        $runStatus = $this->getRunStatus($runId);
        if (!$runStatus['success']) {
            return $runStatus;
        }

        $datasetId = $runStatus['data']['defaultDatasetId'] ?? null;
        if (!$datasetId) {
            return [
                'success' => false,
                'error' => 'No dataset ID found for run',
                'items' => []
            ];
        }

        $url = "{$this->baseUrl}/datasets/{$datasetId}/items?token={$this->apiToken}&limit={$limit}&offset={$offset}&format=json";
        
        $response = $this->makeRequest('GET', $url);
        
        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to fetch dataset items',
                'items' => []
            ];
        }

        return [
            'success' => true,
            'items' => $response['data'] ?? [],
            'count' => count($response['data'] ?? [])
        ];
    }

    /**
     * Run the full collection workflow: start -> wait -> fetch
     */
    public function runCollection(array $targets, array $options = []): array
    {
        $input = $this->buildInput($targets, $options);

        // Start the run
        $startResult = $this->startRun($input);
        if (!$startResult['success']) {
            return $startResult;
        }

        $runId = $startResult['run_id'];

        // Wait for completion
        $waitResult = $this->waitForRun($runId);
        
        if ($waitResult['status'] !== 'SUCCEEDED') {
            return [
                'success' => false,
                'error' => "Run did not succeed. Status: " . $waitResult['status'],
                'run_id' => $runId,
                'items' => []
            ];
        }

        // Fetch results
        return $this->fetchDatasetItems($runId);
    }

    /**
     * Map Apify output to our post data structure
     */
    public function mapPostData(array $apifyItem, int $projectId, ?int $collectionRunId = null): array
    {
        return [
            'project_id' => $projectId,
            'collection_run_id' => $collectionRunId,
            'platform' => 'x_twitter',
            'external_post_id' => $apifyItem['id'] ?? null,
            'post_url' => $apifyItem['url'] ?? null,
            'author_name' => $apifyItem['name'] ?? null,
            'author_username' => $apifyItem['user_posted'] ?? null,
            'author_followers' => $apifyItem['followers'] ?? null,
            'author_verified' => !empty($apifyItem['is_verified']) ? 1 : 0,
            'author_bio' => $apifyItem['biography'] ?? null,
            'content_text' => $apifyItem['description'] ?? '',
            'posted_at' => $apifyItem['date_posted'] ?? null,
            'likes_count' => $apifyItem['likes'] ?? 0,
            'replies_count' => $apifyItem['replies'] ?? 0,
            'reposts_count' => $apifyItem['reposts'] ?? 0,
            'quotes_count' => $apifyItem['quotes'] ?? 0,
            'views_count' => $apifyItem['views'] ?? 0,
            'bookmarks_count' => $apifyItem['bookmarks'] ?? 0,
            'language' => null, // Will be detected by AI
            'hashtags' => isset($apifyItem['hashtags']) ? json_encode($apifyItem['hashtags'], JSON_UNESCAPED_UNICODE) : null,
            'tagged_users' => isset($apifyItem['tagged_users']) ? json_encode($apifyItem['tagged_users'], JSON_UNESCAPED_UNICODE) : null,
            'photos' => isset($apifyItem['photos']) ? json_encode(is_array($apifyItem['photos']) ? $apifyItem['photos'] : [$apifyItem['photos']], JSON_UNESCAPED_UNICODE) : null,
            'videos' => isset($apifyItem['videos']) ? json_encode(is_array($apifyItem['videos']) ? $apifyItem['videos'] : [$apifyItem['videos']], JSON_UNESCAPED_UNICODE) : null,
            'raw_json' => json_encode($apifyItem, JSON_UNESCAPED_UNICODE)
        ];
    }

    /**
     * Make HTTP request with retry logic
     */
    private function makeRequest(string $method, string $url, ?array $body = null): array
    {
        $attempt = 0;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json'
            ];
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($body !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
                }
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("Apify cURL error (attempt {$attempt}): {$curlError}");
                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay);
                    continue;
                }
                return [
                    'success' => false,
                    'error' => "cURL error: {$curlError}"
                ];
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Apify JSON decode error: " . json_last_error_msg());
                return [
                    'success' => false,
                    'error' => 'Invalid JSON response from Apify'
                ];
            }

            if ($httpCode >= 400) {
                $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
                error_log("Apify API error (attempt {$attempt}): HTTP {$httpCode} - {$errorMsg}");
                
                // Don't retry client errors (4xx)
                if ($httpCode < 500) {
                    return [
                        'success' => false,
                        'error' => $errorMsg
                    ];
                }
                
                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay);
                    continue;
                }
                
                return [
                    'success' => false,
                    'error' => $errorMsg
                ];
            }

            return [
                'success' => true,
                'data' => $data
            ];
        }

        return [
            'success' => false,
            'error' => 'Max retries exceeded'
        ];
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        $url = "{$this->baseUrl}/users?token={$this->apiToken}";
        $response = $this->makeRequest('GET', $url);
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'تم الاتصال بنجاح بـ Apify',
                'user' => $response['data']['username'] ?? 'N/A'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'فشل الاتصال بـ Apify: ' . ($response['error'] ?? 'خطأ غير معروف')
        ];
    }
}
