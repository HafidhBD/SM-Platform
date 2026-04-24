<?php
/**
 * Post Model
 */
class PostModel extends BaseModel
{
    protected string $table = 'posts';

    /**
     * Store posts from Apify, avoiding duplicates
     */
    public function storeFromApify(array $apifyItems, int $projectId, ?int $runId = null): array
    {
        $apifyService = new ApifyService();
        $stored = 0;
        $duplicates = 0;
        $errors = 0;

        foreach ($apifyItems as $item) {
            $mapped = $apifyService->mapPostData($item, $projectId, $runId);
            
            if (empty($mapped['content_text'])) {
                $errors++;
                continue;
            }

            // Check for duplicate by external_post_id
            if ($mapped['external_post_id']) {
                $existing = $this->db->queryOne(
                    "SELECT id FROM {$this->table} WHERE project_id = ? AND external_post_id = ?",
                    [$projectId, $mapped['external_post_id']]
                );
                if ($existing) {
                    $duplicates++;
                    // Update existing post with new metrics
                    $this->db->update($this->table, [
                        'likes_count' => $mapped['likes_count'],
                        'replies_count' => $mapped['replies_count'],
                        'reposts_count' => $mapped['reposts_count'],
                        'views_count' => $mapped['views_count'],
                        'quotes_count' => $mapped['quotes_count'],
                        'bookmarks_count' => $mapped['bookmarks_count'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$existing['id']]);
                    continue;
                }
            }

            $id = $this->db->insert($this->table, $mapped);
            if ($id > 0) {
                $stored++;
            } else {
                $errors++;
            }
        }

        return [
            'stored' => $stored,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'total' => count($apifyItems)
        ];
    }

    /**
     * Get posts with AI analysis joined
     */
    public function getWithAnalysis(int $projectId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = "p.project_id = :project_id AND p.deleted_at IS NULL";
        $params = ['project_id' => $projectId];

        if (!empty($filters['sentiment'])) {
            $where .= " AND pa.sentiment = :sentiment";
            $params['sentiment'] = $filters['sentiment'];
        }
        if (!empty($filters['topic'])) {
            $where .= " AND pa.topic_label = :topic";
            $params['topic'] = $filters['topic'];
        }
        if (!empty($filters['reputation'])) {
            $where .= " AND pa.reputation_label = :reputation";
            $params['reputation'] = $filters['reputation'];
        }
        if (!empty($filters['risk_min'])) {
            $where .= " AND pa.risk_score >= :risk_min";
            $params['risk_min'] = (int) $filters['risk_min'];
        }
        if (!empty($filters['author'])) {
            $where .= " AND p.author_username LIKE :author";
            $params['author'] = '%' . $filters['author'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND p.posted_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND p.posted_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND p.content_text LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['crisis_flag'])) {
            $where .= " AND pa.crisis_flag = 1";
        }

        $countSql = "SELECT COUNT(*) as cnt FROM posts p LEFT JOIN post_ai_analysis pa ON p.id = pa.post_id WHERE {$where}";
        $countResult = $this->db->queryOne($countSql, $params);
        $total = $countResult ? (int) $countResult['cnt'] : 0;
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $orderBy = 'p.posted_at DESC';
        if (!empty($filters['sort'])) {
            $allowedSorts = ['p.posted_at DESC', 'p.posted_at ASC', 'p.likes_count DESC', 'p.replies_count DESC', 'pa.risk_score DESC', 'pa.sentiment_score ASC'];
            if (in_array($filters['sort'], $allowedSorts)) {
                $orderBy = $filters['sort'];
            }
        }

        $sql = "SELECT p.*, pa.sentiment, pa.sentiment_score, pa.reputation_label, 
                pa.crisis_flag, pa.attack_flag, pa.complaint_flag, pa.sarcasm_flag,
                pa.topic_label, pa.risk_score, pa.ai_summary, pa.ai_keywords, pa.analyzed_at
                FROM posts p 
                LEFT JOIN post_ai_analysis pa ON p.id = pa.post_id 
                WHERE {$where} 
                ORDER BY {$orderBy} 
                LIMIT {$perPage} OFFSET {$offset}";

        $items = $this->db->query($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }

    /**
     * Get posts without analysis (for AI processing)
     */
    public function getUnanalyzed(int $projectId, int $limit = 100): array
    {
        return $this->db->query(
            "SELECT p.* FROM posts p 
             LEFT JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND pa.id IS NULL AND p.deleted_at IS NULL 
             ORDER BY p.posted_at DESC LIMIT ?",
            [$projectId, $limit]
        );
    }

    /**
     * Get sentiment distribution for a project
     */
    public function getSentimentDistribution(int $projectId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = "p.project_id = ? AND p.deleted_at IS NULL AND pa.sentiment IS NOT NULL";
        $params = [$projectId];

        if ($dateFrom) {
            $where .= " AND p.posted_at >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND p.posted_at <= ?";
            $params[] = $dateTo;
        }

        return $this->db->query(
            "SELECT pa.sentiment, COUNT(*) as count 
             FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE {$where} 
             GROUP BY pa.sentiment",
            $params
        );
    }

    /**
     * Get top topics
     */
    public function getTopTopics(int $projectId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT pa.topic_label, COUNT(*) as count 
             FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND pa.topic_label IS NOT NULL AND p.deleted_at IS NULL 
             GROUP BY pa.topic_label 
             ORDER BY count DESC LIMIT ?",
            [$projectId, $limit]
        );
    }

    /**
     * Get top authors by engagement
     */
    public function getTopAuthors(int $projectId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT p.author_username, p.author_name, 
                    COUNT(*) as post_count, 
                    SUM(p.likes_count) as total_likes,
                    SUM(p.replies_count) as total_replies,
                    SUM(p.reposts_count) as total_reposts,
                    MAX(p.author_followers) as followers
             FROM posts p 
             WHERE p.project_id = ? AND p.deleted_at IS NULL AND p.author_username IS NOT NULL 
             GROUP BY p.author_username 
             ORDER BY total_likes DESC LIMIT ?",
            [$projectId, $limit]
        );
    }

    /**
     * Get most engaged posts
     */
    public function getMostEngaged(int $projectId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT p.*, pa.sentiment, pa.reputation_label, pa.risk_score
             FROM posts p LEFT JOIN post_ai_analysis pa ON p.id = pa.post_id
             WHERE p.project_id = ? AND p.deleted_at IS NULL 
             ORDER BY (p.likes_count + p.replies_count + p.reposts_count) DESC 
             LIMIT ?",
            [$projectId, $limit]
        );
    }

    /**
     * Get posts timeline data for charts
     */
    public function getTimeline(int $projectId, string $interval = 'day', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFormat = match ($interval) {
            'hour' => '%Y-%m-%d %H:00',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $where = "project_id = ? AND deleted_at IS NULL";
        $params = [$projectId];
        if ($dateFrom) {
            $where .= " AND posted_at >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND posted_at <= ?";
            $params[] = $dateTo;
        }

        return $this->db->query(
            "SELECT DATE_FORMAT(posted_at, '{$dateFormat}') as period, COUNT(*) as count 
             FROM posts WHERE {$where} 
             GROUP BY period ORDER BY period",
            $params
        );
    }

    /**
     * Get sentiment timeline for charts
     */
    public function getSentimentTimeline(int $projectId, string $interval = 'day', ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFormat = match ($interval) {
            'hour' => '%Y-%m-%d %H:00',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $where = "p.project_id = ? AND p.deleted_at IS NULL AND pa.sentiment IS NOT NULL";
        $params = [$projectId];
        if ($dateFrom) {
            $where .= " AND p.posted_at >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND p.posted_at <= ?";
            $params[] = $dateTo;
        }

        return $this->db->query(
            "SELECT DATE_FORMAT(p.posted_at, '{$dateFormat}') as period, 
                    pa.sentiment, COUNT(*) as count 
             FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE {$where} 
             GROUP BY period, pa.sentiment 
             ORDER BY period",
            $params
        );
    }

    /**
     * Get most frequent words from post content
     */
    public function getTopWords(int $projectId, int $limit = 20): array
    {
        // Get AI-extracted keywords
        $results = $this->db->query(
            "SELECT pa.ai_keywords FROM posts p 
             JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND pa.ai_keywords IS NOT NULL AND p.deleted_at IS NULL 
             LIMIT 500",
            [$projectId]
        );

        $wordCount = [];
        foreach ($results as $row) {
            $keywords = json_decode($row['ai_keywords'], true);
            if (!is_array($keywords)) continue;
            foreach ($keywords as $word) {
                $word = trim($word);
                if (mb_strlen($word) < 2) continue;
                $wordCount[$word] = ($wordCount[$word] ?? 0) + 1;
            }
        }

        arsort($wordCount);
        return array_slice($wordCount, 0, $limit, true);
    }

    /**
     * Get dashboard stats for a project
     */
    public function getDashboardStats(int $projectId): array
    {
        $total = $this->db->count('posts', 'project_id = ? AND deleted_at IS NULL', [$projectId]);
        
        $sentiment = $this->db->query(
            "SELECT pa.sentiment, COUNT(*) as count 
             FROM posts p JOIN post_ai_analysis pa ON p.id = pa.post_id 
             WHERE p.project_id = ? AND p.deleted_at IS NULL AND pa.sentiment IS NOT NULL 
             GROUP BY pa.sentiment",
            [$projectId]
        );

        $positiveCount = 0;
        $negativeCount = 0;
        $neutralCount = 0;
        foreach ($sentiment as $s) {
            match ($s['sentiment']) {
                'positive' => $positiveCount = (int) $s['count'],
                'negative' => $negativeCount = (int) $s['count'],
                'neutral' => $neutralCount = (int) $s['count'],
                default => 0
            };
        }

        $negativePercent = $total > 0 ? round(($negativeCount / max($positiveCount + $negativeCount + $neutralCount, 1)) * 100, 1) : 0;

        $lastCollection = $this->db->queryOne(
            "SELECT * FROM collection_runs WHERE project_id = ? ORDER BY created_at DESC LIMIT 1",
            [$projectId]
        );

        $lastAnalysis = $this->db->queryOne(
            "SELECT MAX(analyzed_at) as last_analyzed FROM post_ai_analysis pa 
             JOIN posts p ON pa.post_id = p.id 
             WHERE p.project_id = ?",
            [$projectId]
        );

        $alertCount = $this->db->count('alerts', 'project_id = ? AND is_resolved = 0', [$projectId]);

        return [
            'total_posts' => $total,
            'positive_count' => $positiveCount,
            'negative_count' => $negativeCount,
            'neutral_count' => $neutralCount,
            'negative_percent' => $negativePercent,
            'alerts_count' => $alertCount,
            'last_collection' => $lastCollection,
            'last_analysis' => $lastAnalysis['last_analyzed'] ?? null
        ];
    }
}
