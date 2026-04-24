<?php
/**
 * Alert Model
 */
class AlertModel extends BaseModel
{
    protected string $table = 'alerts';

    public function createAlert(int $projectId, string $type, string $severity, string $title, string $description, array $evidence = []): int
    {
        // Check for similar unread alert in last hour to avoid duplicates
        $existing = $this->db->queryOne(
            "SELECT id FROM {$this->table} 
             WHERE project_id = ? AND alert_type = ? AND is_read = 0 AND is_resolved = 0 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$projectId, $type]
        );
        if ($existing) return $existing['id'];

        return $this->db->insert($this->table, [
            'project_id' => $projectId,
            'alert_type' => $type,
            'severity' => $severity,
            'title' => $title,
            'description' => $description,
            'evidence' => json_encode($evidence, JSON_UNESCAPED_UNICODE),
            'is_read' => 0,
            'is_resolved' => 0,
            'triggered_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getByProject(int $projectId, string $severity = '', int $limit = 50): array
    {
        $where = "project_id = ?";
        $params = [$projectId];
        if ($severity) {
            $where .= " AND severity = ?";
            $params[] = $severity;
        }
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT ?",
            array_merge($params, [$limit])
        );
    }

    public function getUnread(int $projectId = 0): array
    {
        if ($projectId > 0) {
            return $this->db->query(
                "SELECT * FROM {$this->table} WHERE project_id = ? AND is_read = 0 ORDER BY created_at DESC",
                [$projectId]
            );
        }
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE is_read = 0 ORDER BY created_at DESC"
        );
    }

    public function getUnresolved(int $projectId = 0): array
    {
        if ($projectId > 0) {
            return $this->db->query(
                "SELECT * FROM {$this->table} WHERE project_id = ? AND is_resolved = 0 ORDER BY severity DESC, created_at DESC",
                [$projectId]
            );
        }
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE is_resolved = 0 ORDER BY severity DESC, created_at DESC"
        );
    }

    public function markRead(int $alertId): int
    {
        return $this->db->update($this->table, ['is_read' => 1], 'id = ?', [$alertId]);
    }

    public function markAllRead(int $projectId): int
    {
        return $this->db->update($this->table, ['is_read' => 1], 'project_id = ? AND is_read = 0', [$projectId]);
    }

    public function resolve(int $alertId, ?int $userId = null): int
    {
        $data = ['is_resolved' => 1, 'resolved_at' => date('Y-m-d H:i:s')];
        if ($userId) $data['resolved_by'] = $userId;
        return $this->db->update($this->table, $data, 'id = ?', [$alertId]);
    }

    public function countBySeverity(int $projectId = 0): array
    {
        if ($projectId > 0) {
            $results = $this->db->query(
                "SELECT severity, COUNT(*) as count FROM {$this->table} WHERE project_id = ? AND is_resolved = 0 GROUP BY severity",
                [$projectId]
            );
        } else {
            $results = $this->db->query(
                "SELECT severity, COUNT(*) as count FROM {$this->table} WHERE is_resolved = 0 GROUP BY severity"
            );
        }
        $counts = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        foreach ($results as $r) {
            $counts[$r['severity']] = (int) $r['count'];
        }
        return $counts;
    }
}
