<?php
/**
 * OpenAI API Service
 * 
 * Handles communication with OpenAI API for:
 * - Sentiment analysis
 * - Reputation analysis
 * - Topic classification
 * - Crisis detection
 * - Executive summaries
 * - Campaign insights
 */
class OpenAIService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $maxTokens;
    private float $temperature;
    private int $batchSize;
    private int $maxRetries;

    public function __construct()
    {
        $this->apiKey = getSetting('openai_api_key', OPENAI_API_KEY);
        $this->model = getSetting('openai_model', OPENAI_MODEL);
        $this->baseUrl = OPENAI_BASE_URL;
        $this->maxTokens = OPENAI_MAX_TOKENS;
        $this->temperature = OPENAI_TEMPERATURE;
        $this->batchSize = (int) getSetting('openai_batch_size', OPENAI_BATCH_SIZE);
        $this->maxRetries = OPENAI_MAX_RETRIES;
    }

    /**
     * Analyze a batch of posts - combined analysis for cost efficiency
     */
    public function analyzePostsBatch(array $posts, ?string $projectName = null): array
    {
        $prompt = $this->loadPrompt('post_analysis');
        if (!$prompt) {
            return ['success' => false, 'error' => 'Prompt file not found'];
        }

        // Prepare posts text for the prompt
        $postsText = '';
        foreach ($posts as $i => $post) {
            $postsText .= "\n--- Post #" . ($i + 1) . " ---\n";
            $postsText .= "Text: " . ($post['content_text'] ?? '') . "\n";
            $postsText .= "Author: @" . ($post['author_username'] ?? 'unknown') . "\n";
            $postsText .= "Likes: " . ($post['likes_count'] ?? 0) . " | Replies: " . ($post['replies_count'] ?? 0) . " | Reposts: " . ($post['reposts_count'] ?? 0) . "\n";
        }

        $systemPrompt = str_replace('{PROJECT_NAME}', $projectName ?? 'الجهة', $prompt);
        $userMessage = "Analyze the following posts and return JSON array:\n{$postsText}";

        $response = $this->chat($systemPrompt, $userMessage);
        
        if (!$response['success']) {
            return $response;
        }

        $analysisResults = $this->parseAnalysisResponse($response['content'], count($posts));
        
        return [
            'success' => true,
            'results' => $analysisResults,
            'model' => $this->model,
            'tokens_used' => $response['usage']['total_tokens'] ?? 0
        ];
    }

    /**
     * Generate executive summary
     */
    public function generateSummary(array $context): array
    {
        $prompt = $this->loadPrompt('executive_summary');
        if (!$prompt) {
            return ['success' => false, 'error' => 'Summary prompt not found'];
        }

        $systemPrompt = str_replace(
            ['{PROJECT_NAME}', '{PERIOD}'],
            [$context['project_name'] ?? 'الجهة', $context['period'] ?? 'الفترة المحددة'],
            $prompt
        );

        $userMessage = "Here is the data for analysis:\n\n";
        $userMessage .= "Total posts: " . ($context['total_posts'] ?? 0) . "\n";
        $userMessage .= "Positive: " . ($context['positive_count'] ?? 0) . "\n";
        $userMessage .= "Negative: " . ($context['negative_count'] ?? 0) . "\n";
        $userMessage .= "Neutral: " . ($context['neutral_count'] ?? 0) . "\n";
        $userMessage .= "Negative %: " . ($context['negative_percent'] ?? 0) . "%\n\n";
        
        if (!empty($context['top_topics'])) {
            $userMessage .= "Top topics: " . json_encode($context['top_topics'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['top_keywords'])) {
            $userMessage .= "Top keywords: " . json_encode($context['top_keywords'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['top_accounts'])) {
            $userMessage .= "Top accounts: " . json_encode($context['top_accounts'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['complaints'])) {
            $userMessage .= "Complaints: " . json_encode($context['complaints'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['attacks'])) {
            $userMessage .= "Attacks: " . json_encode($context['attacks'], JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['sample_negative'])) {
            $userMessage .= "Sample negative posts: " . json_encode(array_slice($context['sample_negative'], 0, 10), JSON_UNESCAPED_UNICODE) . "\n";
        }
        if (!empty($context['sample_positive'])) {
            $userMessage .= "Sample positive posts: " . json_encode(array_slice($context['sample_positive'], 0, 10), JSON_UNESCAPED_UNICODE) . "\n";
        }

        $response = $this->chat($systemPrompt, $userMessage);
        
        if (!$response['success']) {
            return $response;
        }

        $summaryData = $this->parseJsonResponse($response['content']);

        return [
            'success' => true,
            'summary' => $summaryData,
            'model' => $this->model,
            'tokens_used' => $response['usage']['total_tokens'] ?? 0
        ];
    }

    /**
     * Detect crisis signals
     */
    public function detectCrisis(array $context): array
    {
        $prompt = $this->loadPrompt('crisis_detection');
        if (!$prompt) {
            return ['success' => false, 'error' => 'Crisis prompt not found'];
        }

        $systemPrompt = str_replace('{PROJECT_NAME}', $context['project_name'] ?? 'الجهة', $prompt);
        $userMessage = json_encode($context, JSON_UNESCAPED_UNICODE);

        $response = $this->chat($systemPrompt, $userMessage);
        
        if (!$response['success']) {
            return $response;
        }

        $crisisData = $this->parseJsonResponse($response['content']);

        return [
            'success' => true,
            'crisis' => $crisisData,
            'model' => $this->model,
            'tokens_used' => $response['usage']['total_tokens'] ?? 0
        ];
    }

    /**
     * Generate campaign insights
     */
    public function generateCampaignInsights(array $context): array
    {
        $prompt = $this->loadPrompt('campaign_insights');
        if (!$prompt) {
            return ['success' => false, 'error' => 'Campaign insights prompt not found'];
        }

        $systemPrompt = str_replace('{PROJECT_NAME}', $context['project_name'] ?? 'الجهة', $prompt);
        $userMessage = json_encode($context, JSON_UNESCAPED_UNICODE);

        $response = $this->chat($systemPrompt, $userMessage);
        
        if (!$response['success']) {
            return $response;
        }

        $insightsData = $this->parseJsonResponse($response['content']);

        return [
            'success' => true,
            'insights' => $insightsData,
            'model' => $this->model,
            'tokens_used' => $response['usage']['total_tokens'] ?? 0
        ];
    }

    /**
     * Send chat completion request to OpenAI
     */
    private function chat(string $systemPrompt, string $userMessage): array
    {
        $url = "{$this->baseUrl}/chat/completions";
        
        $body = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'response_format' => ['type' => 'json_object']
        ];

        $attempt = 0;
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                error_log("OpenAI cURL error (attempt {$attempt}): {$curlError}");
                if ($attempt < $this->maxRetries) {
                    sleep(2);
                    continue;
                }
                return ['success' => false, 'error' => "cURL error: {$curlError}"];
            }

            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid JSON from OpenAI'];
            }

            if ($httpCode >= 400) {
                $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
                error_log("OpenAI API error: HTTP {$httpCode} - {$errorMsg}");
                
                // Rate limit - wait and retry
                if ($httpCode === 429 && $attempt < $this->maxRetries) {
                    sleep(5);
                    continue;
                }
                
                return ['success' => false, 'error' => $errorMsg];
            }

            $content = $data['choices'][0]['message']['content'] ?? '';
            return [
                'success' => true,
                'content' => $content,
                'usage' => $data['usage'] ?? []
            ];
        }

        return ['success' => false, 'error' => 'Max retries exceeded'];
    }

    /**
     * Load prompt from file
     */
    private function loadPrompt(string $name): ?string
    {
        $file = PROMPTS_PATH . '/' . $name . '.txt';
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        error_log("Prompt file not found: {$file}");
        return null;
    }

    /**
     * Parse analysis response for batch posts
     */
    private function parseAnalysisResponse(string $content, int $expectedCount): array
    {
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to parse analysis JSON: " . json_last_error_msg());
            return [];
        }

        // Handle both array and object with results key
        if (isset($decoded['results'])) {
            return $decoded['results'];
        }
        if (isset($decoded['analyses'])) {
            return $decoded['analyses'];
        }
        if (is_array($decoded) && isset($decoded[0])) {
            return $decoded;
        }

        return [$decoded];
    }

    /**
     * Parse JSON response from OpenAI
     */
    private function parseJsonResponse(string $content): array
    {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to parse OpenAI JSON: " . json_last_error_msg());
            return ['raw' => $content];
        }
        return is_array($decoded) ? $decoded : ['raw' => $content];
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'مفتاح OpenAI غير مضبوط'
            ];
        }

        $url = "{$this->baseUrl}/models";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            return [
                'success' => true,
                'message' => 'تم الاتصال بنجاح بـ OpenAI'
            ];
        }

        return [
            'success' => false,
            'message' => 'فشل الاتصال بـ OpenAI (HTTP ' . $httpCode . ')'
        ];
    }

    /**
     * Get batch size
     */
    public function getBatchSize(): int
    {
        return $this->batchSize;
    }
}
