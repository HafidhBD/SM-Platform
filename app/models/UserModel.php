<?php
/**
 * User Model
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM {$this->table} WHERE username = ? AND deleted_at IS NULL",
            [$username]
        );
    }

    public function updatePassword(int $userId, string $newPassword): int
    {
        return $this->db->update(
            $this->table,
            ['password' => password_hash($newPassword, PASSWORD_DEFAULT)],
            'id = ?',
            [$userId]
        );
    }

    public function updateProfile(int $userId, array $data): int
    {
        $allowed = ['display_name', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowed));
        if (empty($updateData)) return 0;
        return $this->update($userId, $updateData);
    }
}
