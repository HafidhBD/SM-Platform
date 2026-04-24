<?php
/**
 * Base Model - All models extend this
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    public function findAll(string $where = '1=1', array $params = [], string $orderBy = ''): array
    {
        if (strpos($where, 'deleted_at') === false) {
            $where .= " AND deleted_at IS NULL";
        }
        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->query($sql, $params);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function delete(int $id, bool $soft = true): int
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id], $soft);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        if (strpos($where, 'deleted_at') === false) {
            $where .= " AND deleted_at IS NULL";
        }
        return $this->db->count($this->table, $where, $params);
    }

    public function paginate(string $where = '1=1', array $params = [], int $page = 1, int $perPage = 20, string $orderBy = ''): array
    {
        if (strpos($where, 'deleted_at') === false) {
            $where .= " AND deleted_at IS NULL";
        }
        $total = $this->db->count($this->table, $where, $params);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE {$where}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $items = $this->db->query($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages
        ];
    }
}
