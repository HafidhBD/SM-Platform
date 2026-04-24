<?php
/**
 * Project Model - Brands/Entities to monitor
 */
class ProjectModel extends BaseModel
{
    protected string $table = 'projects';

    public function getWithStats(int $projectId): ?array
    {
        $project = $this->find($projectId);
        if (!$project) return null;

        $project['keywords'] = $this->db->query(
            "SELECT * FROM project_keywords WHERE project_id = ?",
            [$projectId]
        );
        $project['competitors'] = $this->db->query(
            "SELECT * FROM competitors WHERE project_id = ?",
            [$projectId]
        );
        $project['hashtags'] = $this->db->query(
            "SELECT * FROM project_hashtags WHERE project_id = ?",
            [$projectId]
        );
        $project['accounts'] = $this->db->query(
            "SELECT * FROM project_accounts WHERE project_id = ?",
            [$projectId]
        );
        $project['posts_count'] = $this->db->count('posts', 'project_id = ? AND deleted_at IS NULL', [$projectId]);
        $project['negative_count'] = $this->db->count(
            'post_ai_analysis pa JOIN posts p ON pa.post_id = p.id',
            'p.project_id = ? AND pa.sentiment = ? AND p.deleted_at IS NULL',
            [$projectId, 'negative']
        );
        $project['alerts_count'] = $this->db->count(
            'alerts',
            'project_id = ? AND is_resolved = 0',
            [$projectId]
        );

        return $project;
    }

    public function getAllActive(): array
    {
        return $this->findAll('is_active = 1', [], 'name ASC');
    }

    public function addKeywords(int $projectId, array $keywords, string $type = 'search'): void
    {
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (empty($keyword)) continue;
            $this->db->insert('project_keywords', [
                'project_id' => $projectId,
                'keyword' => $keyword,
                'type' => $type
            ]);
        }
    }

    public function clearKeywords(int $projectId, ?string $type = null): void
    {
        if ($type) {
            $this->db->execute(
                "DELETE FROM project_keywords WHERE project_id = ? AND type = ?",
                [$projectId, $type]
            );
        } else {
            $this->db->execute(
                "DELETE FROM project_keywords WHERE project_id = ?",
                [$projectId]
            );
        }
    }

    public function addCompetitors(int $projectId, array $competitors): void
    {
        foreach ($competitors as $comp) {
            $name = trim($comp['name'] ?? '');
            if (empty($name)) continue;
            $this->db->insert('competitors', [
                'project_id' => $projectId,
                'name' => $name,
                'username' => trim($comp['username'] ?? ''),
                'notes' => trim($comp['notes'] ?? '')
            ]);
        }
    }

    public function clearCompetitors(int $projectId): void
    {
        $this->db->execute("DELETE FROM competitors WHERE project_id = ?", [$projectId]);
    }

    public function addHashtags(int $projectId, array $hashtags): void
    {
        foreach ($hashtags as $hashtag) {
            $hashtag = trim($hashtag);
            if (empty($hashtag)) continue;
            if (strpos($hashtag, '#') !== 0) $hashtag = '#' . $hashtag;
            $this->db->insert('project_hashtags', [
                'project_id' => $projectId,
                'hashtag' => $hashtag
            ]);
        }
    }

    public function clearHashtags(int $projectId): void
    {
        $this->db->execute("DELETE FROM project_hashtags WHERE project_id = ?", [$projectId]);
    }

    public function addAccounts(int $projectId, array $accounts): void
    {
        foreach ($accounts as $account) {
            $username = trim($account['username'] ?? $account);
            if (empty($username)) continue;
            if (strpos($username, '@') !== 0) $username = '@' . $username;
            $this->db->insert('project_accounts', [
                'project_id' => $projectId,
                'account_username' => $username,
                'account_name' => trim(is_array($account) ? ($account['name'] ?? '') : '')
            ]);
        }
    }

    public function clearAccounts(int $projectId): void
    {
        $this->db->execute("DELETE FROM project_accounts WHERE project_id = ?", [$projectId]);
    }

    /**
     * Get all search targets for a project (keywords + hashtags + accounts)
     */
    public function getSearchTargets(int $projectId): array
    {
        $targets = [];
        
        $keywords = $this->db->query(
            "SELECT keyword FROM project_keywords WHERE project_id = ? AND type = 'search'",
            [$projectId]
        );
        foreach ($keywords as $k) {
            $targets[] = $k['keyword'];
        }

        $hashtags = $this->db->query(
            "SELECT hashtag FROM project_hashtags WHERE project_id = ?",
            [$projectId]
        );
        foreach ($hashtags as $h) {
            $targets[] = $h['hashtag'];
        }

        $accounts = $this->db->query(
            "SELECT account_username FROM project_accounts WHERE project_id = ?",
            [$projectId]
        );
        foreach ($accounts as $a) {
            $targets[] = $a['account_username'];
        }

        return $targets;
    }

    /**
     * Get crisis keywords for a project
     */
    public function getCrisisKeywords(int $projectId): array
    {
        $keywords = $this->db->query(
            "SELECT keyword FROM project_keywords WHERE project_id = ? AND type = 'crisis'",
            [$projectId]
        );
        return array_map(fn($k) => $k['keyword'], $keywords);
    }
}
