<?php
/**
 * AI Summary Model
 */
class SummaryModel extends BaseModel
{
    protected string $table = 'ai_summaries';

    public function getByProject(int $projectId, int $limit = 20): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE project_id = ? ORDER BY created_at DESC LIMIT ?",
            [$projectId, $limit]
        );
    }

    /**
     * Generate and store a new summary
     */
    public function generateSummary(int $projectId, string $type = 'manual', ?string $periodStart = null, ?string $periodEnd = null): array
    {
        $postModel = new PostModel();
        $projectModel = new ProjectModel();
        $openai = new OpenAIService();
        $project = $projectModel->find($projectId);

        if (!$project) {
            return ['success' => false, 'error' => 'المشروع غير موجود'];
        }

        $stats = $postModel->getDashboardStats($projectId);
        $sentiment = $postModel->getSentimentDistribution($projectId, $periodStart, $periodEnd);
        $topics = $postModel->getTopTopics($projectId, 10);
        $words = $postModel->getTopWords($projectId, 20);
        $authors = $postModel->getTopAuthors($projectId, 5);

        // Get sample posts
        $sampleNegative = $this->db->query(
            "SELECT p.content_text FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND pa.sentiment = 'negative' AND p.deleted_at IS NULL 
             ORDER BY p.likes_count DESC LIMIT 10",
            [$projectId]
        );
        $samplePositive = $this->db->query(
            "SELECT p.content_text FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND pa.sentiment = 'positive' AND p.deleted_at IS NULL 
             ORDER BY p.likes_count DESC LIMIT 10",
            [$projectId]
        );

        $context = [
            'project_name' => $project['name'],
            'period' => $periodStart && $periodEnd ? "{$periodStart} إلى {$periodEnd}" : 'الفترة الحالية',
            'total_posts' => $stats['total_posts'],
            'positive_count' => $stats['positive_count'],
            'negative_count' => $stats['negative_count'],
            'neutral_count' => $stats['neutral_count'],
            'negative_percent' => $stats['negative_percent'],
            'top_topics' => array_map(fn($t) => ['topic' => $t['topic_label'], 'count' => $t['count']], $topics),
            'top_keywords' => array_keys($words),
            'top_accounts' => array_map(fn($a) => ['username' => $a['author_username'], 'engagement' => $a['total_likes']], $authors),
            'sample_negative' => array_map(fn($p) => $p['content_text'], $sampleNegative),
            'sample_positive' => array_map(fn($p) => $p['content_text'], $samplePositive)
        ];

        $result = $openai->generateSummary($context);

        if (!$result['success']) {
            return $result;
        }

        $summary = $result['summary'];
        $id = $this->db->insert($this->table, [
            'project_id' => $projectId,
            'summary_type' => $type,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'executive_summary' => $summary['executive_summary'] ?? null,
            'top_negative_points' => isset($summary['top_negative_points']) ? json_encode($summary['top_negative_points'], JSON_UNESCAPED_UNICODE) : null,
            'top_positive_points' => isset($summary['top_positive_points']) ? json_encode($summary['top_positive_points'], JSON_UNESCAPED_UNICODE) : null,
            'recommendations' => isset($summary['recommendations']) ? json_encode($summary['recommendations'], JSON_UNESCAPED_UNICODE) : null,
            'campaign_opportunities' => isset($summary['campaign_opportunities']) ? json_encode($summary['campaign_opportunities'], JSON_UNESCAPED_UNICODE) : null,
            'audience_interests' => isset($summary['audience_interests']) ? json_encode($summary['audience_interests'], JSON_UNESCAPED_UNICODE) : null,
            'repeated_messages' => isset($summary['repeated_messages']) ? json_encode($summary['repeated_messages'], JSON_UNESCAPED_UNICODE) : null,
            'market_gaps' => isset($summary['market_gaps']) ? json_encode($summary['market_gaps'], JSON_UNESCAPED_UNICODE) : null,
            'reputation_status' => $summary['reputation_status'] ?? null,
            'analysis_model' => $result['model'] ?? null,
            'posts_analyzed' => $stats['total_posts']
        ]);

        return [
            'success' => true,
            'summary_id' => $id,
            'summary' => $summary,
            'tokens_used' => $result['tokens_used'] ?? 0
        ];
    }
}
