<?php
/**
 * AI Analysis Model
 */
class AnalysisModel extends BaseModel
{
    protected string $table = 'post_ai_analysis';

    /**
     * Store analysis results for a post
     */
    public function storeAnalysis(int $postId, array $analysis, string $model = '', ?string $batchId = null): int
    {
        // Check if already analyzed
        $existing = $this->db->queryOne(
            "SELECT id FROM {$this->table} WHERE post_id = ?",
            [$postId]
        );
        if ($existing) {
            // Update existing analysis
            $this->db->update($this->table, [
                'sentiment' => $analysis['sentiment'] ?? null,
                'sentiment_score' => $analysis['sentiment_score'] ?? null,
                'reputation_label' => $analysis['reputation_label'] ?? null,
                'crisis_flag' => !empty($analysis['crisis_flag']) ? 1 : 0,
                'attack_flag' => !empty($analysis['attack_flag']) ? 1 : 0,
                'complaint_flag' => !empty($analysis['complaint_flag']) ? 1 : 0,
                'sarcasm_flag' => !empty($analysis['sarcasm_flag']) ? 1 : 0,
                'topic_label' => $analysis['topic_label'] ?? null,
                'risk_score' => $analysis['risk_score'] ?? 0,
                'ai_summary' => $analysis['ai_summary'] ?? null,
                'ai_keywords' => isset($analysis['ai_keywords']) ? json_encode($analysis['ai_keywords'], JSON_UNESCAPED_UNICODE) : null,
                'analysis_model' => $model,
                'analysis_batch_id' => $batchId,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$existing['id']]);
            return $existing['id'];
        }

        return $this->db->insert($this->table, [
            'post_id' => $postId,
            'sentiment' => $analysis['sentiment'] ?? null,
            'sentiment_score' => $analysis['sentiment_score'] ?? null,
            'reputation_label' => $analysis['reputation_label'] ?? null,
            'crisis_flag' => !empty($analysis['crisis_flag']) ? 1 : 0,
            'attack_flag' => !empty($analysis['attack_flag']) ? 1 : 0,
            'complaint_flag' => !empty($analysis['complaint_flag']) ? 1 : 0,
            'sarcasm_flag' => !empty($analysis['sarcasm_flag']) ? 1 : 0,
            'topic_label' => $analysis['topic_label'] ?? null,
            'risk_score' => $analysis['risk_score'] ?? 0,
            'ai_summary' => $analysis['ai_summary'] ?? null,
            'ai_keywords' => isset($analysis['ai_keywords']) ? json_encode($analysis['ai_keywords'], JSON_UNESCAPED_UNICODE) : null,
            'analysis_model' => $model,
            'analysis_batch_id' => $batchId
        ]);
    }

    /**
     * Run analysis on unanalyzed posts for a project
     */
    public function analyzeProjectPosts(int $projectId, bool $forceReanalyze = false): array
    {
        $postModel = new PostModel();
        $openai = new OpenAIService();
        $projectModel = new ProjectModel();
        $project = $projectModel->find($projectId);
        $projectName = $project['name'] ?? 'الجهة';

        if ($forceReanalyze) {
            $posts = $postModel->findAll('project_id = ?', [$projectId], 'posted_at DESC');
        } else {
            $posts = $postModel->getUnanalyzed($projectId, 500);
        }

        if (empty($posts)) {
            return ['success' => true, 'message' => 'لا توجد منشورات جديدة للتحليل', 'analyzed' => 0];
        }

        $batchSize = $openai->getBatchSize();
        $batches = array_chunk($posts, $batchSize);
        $totalAnalyzed = 0;
        $totalTokens = 0;
        $errors = 0;

        foreach ($batches as $batch) {
            $batchId = uniqid('batch_');
            $result = $openai->analyzePostsBatch($batch, $projectName);

            if (!$result['success']) {
                $errors++;
                error_log("Analysis batch failed: " . ($result['error'] ?? 'Unknown'));
                continue;
            }

            $totalTokens += $result['tokens_used'] ?? 0;

            foreach ($result['results'] as $i => $analysis) {
                if (!isset($batch[$i])) break;
                $postId = $batch[$i]['id'];
                $this->storeAnalysis($postId, $analysis, $result['model'] ?? '', $batchId);
                $totalAnalyzed++;
            }
        }

        // After analysis, run crisis detection
        $this->detectCrisisSignals($projectId);

        return [
            'success' => true,
            'analyzed' => $totalAnalyzed,
            'errors' => $errors,
            'tokens_used' => $totalTokens,
            'batches' => count($batches)
        ];
    }

    /**
     * Detect crisis signals based on analysis results
     */
    public function detectCrisisSignals(int $projectId): void
    {
        $alertModel = new AlertModel();
        $projectModel = new ProjectModel();
        $postModel = new PostModel();

        $stats = $postModel->getDashboardStats($projectId);
        $negativeThreshold = (int) getSetting('alert_negative_threshold', ALERT_NEGATIVE_THRESHOLD);
        $volumeSpikePercent = (int) getSetting('alert_volume_spike_percent', ALERT_VOLUME_SPIKE_PERCENT);
        $crisisKeywordThreshold = (int) getSetting('alert_crisis_keyword_threshold', ALERT_CRISIS_KEYWORD_THRESHOLD);
        $attackPostThreshold = (int) getSetting('alert_attack_post_threshold', ALERT_ATTACK_POST_THRESHOLD);

        // Check negative sentiment spike
        if ($stats['negative_percent'] >= $negativeThreshold) {
            $alertModel->createAlert($projectId, 'negative_spike', 'high',
                'ارتفاع نسبة السلبية',
                "نسبة المنشورات السلبية وصلت إلى {$stats['negative_percent']}%",
                ['negative_percent' => $stats['negative_percent'], 'threshold' => $negativeThreshold]
            );
        }

        // Check attack-flagged posts
        $attackCount = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM post_ai_analysis pa 
             JOIN posts p ON pa.post_id = p.id 
             WHERE p.project_id = ? AND pa.attack_flag = 1 AND p.deleted_at IS NULL 
             AND pa.analyzed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$projectId]
        );
        if ($attackCount && (int) $attackCount['cnt'] >= $attackPostThreshold) {
            $alertModel->createAlert($projectId, 'attack_detected', 'critical',
                'هجوم محتمل مكتشف',
                "تم تصنيف {$attackCount['cnt']} منشور كهدجم خلال 24 ساعة",
                ['attack_count' => (int) $attackCount['cnt']]
            );
        }

        // Check complaint surge
        $complaintCount = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM post_ai_analysis pa 
             JOIN posts p ON pa.post_id = p.id 
             WHERE p.project_id = ? AND pa.complaint_flag = 1 AND p.deleted_at IS NULL 
             AND pa.analyzed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$projectId]
        );
        if ($complaintCount && (int) $complaintCount['cnt'] >= $crisisKeywordThreshold) {
            $alertModel->createAlert($projectId, 'complaint_surge', 'medium',
                'ارتفاع الشكاوى',
                "تم رصد {$complaintCount['cnt']} شكوى خلال 24 ساعة",
                ['complaint_count' => (int) $complaintCount['cnt']]
            );
        }

        // Check crisis keywords in content
        $crisisKeywords = $projectModel->getCrisisKeywords($projectId);
        if (!empty($crisisKeywords)) {
            foreach ($crisisKeywords as $keyword) {
                $count = $this->db->queryOne(
                    "SELECT COUNT(*) as cnt FROM posts 
                     WHERE project_id = ? AND content_text LIKE ? AND deleted_at IS NULL 
                     AND posted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                    [$projectId, '%' . $keyword . '%']
                );
                if ($count && (int) $count['cnt'] >= $crisisKeywordThreshold) {
                    $alertModel->createAlert($projectId, 'crisis_keyword', 'high',
                        'كلمة أزمة مكتشفة: ' . $keyword,
                        "تكررت كلمة الأزمة '{$keyword}' " . (int) $count['cnt'] . " مرة خلال 24 ساعة",
                        ['keyword' => $keyword, 'count' => (int) $count['cnt']]
                    );
                }
            }
        }
    }

    /**
     * Get analysis stats for a project
     */
    public function getProjectAnalysisStats(int $projectId): array
    {
        $total = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM posts WHERE project_id = ? AND deleted_at IS NULL",
            [$projectId]
        );
        $analyzed = $this->db->queryOne(
            "SELECT COUNT(*) as cnt FROM post_ai_analysis pa 
             JOIN posts p ON pa.post_id = p.id 
             WHERE p.project_id = ? AND p.deleted_at IS NULL",
            [$projectId]
        );

        return [
            'total_posts' => (int) ($total['cnt'] ?? 0),
            'analyzed_posts' => (int) ($analyzed['cnt'] ?? 0),
            'unanalyzed_posts' => (int) ($total['cnt'] ?? 0) - (int) ($analyzed['cnt'] ?? 0)
        ];
    }
}
