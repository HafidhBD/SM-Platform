<?php
/**
 * Collection Run Model
 */
class CollectionRunModel extends BaseModel
{
    protected string $table = 'collection_runs';

    public function getByProject(int $projectId, int $limit = 20): array
    {
        return $this->db->query(
            "SELECT * FROM {$this->table} WHERE project_id = ? ORDER BY created_at DESC LIMIT ?",
            [$projectId, $limit]
        );
    }

    public function updateStatus(int $runId, string $status, array $extra = []): int
    {
        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        
        if ($status === 'running') {
            $data['started_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'completed' || $status === 'failed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        if (isset($extra['run_id'])) {
            $data['run_id'] = $extra['run_id'];
        }
        if (isset($extra['posts_found'])) {
            $data['posts_found'] = $extra['posts_found'];
        }
        if (isset($extra['posts_stored'])) {
            $data['posts_stored'] = $extra['posts_stored'];
        }
        if (isset($extra['error_message'])) {
            $data['error_message'] = $extra['error_message'];
        }
        if (isset($extra['input_config'])) {
            $data['input_config'] = $extra['input_config'];
        }
        if (isset($extra['targets'])) {
            $data['targets'] = $extra['targets'];
        }

        return $this->db->update($this->table, $data, 'id = ?', [$runId]);
    }

    public function getLatestByProject(int $projectId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE project_id = ? ORDER BY created_at DESC LIMIT 1",
            [$projectId]
        );
    }
}
