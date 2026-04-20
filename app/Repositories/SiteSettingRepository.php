<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SiteSettingRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query('SELECT * FROM site_settings');
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function get(string $key): ?string
    {
        $stmt = $this->conn->prepare('SELECT value FROM site_settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['value'] ?? null;
    }

    public function set(string $key, ?string $value): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO site_settings (`key`, `value`, `type`, updated_at) VALUES (:key, :value, :type, NOW())
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `type` = VALUES(`type`), updated_at = NOW()'
        );

        return $stmt->execute([
            'key' => $key,
            'value' => $value,
            'type' => 'text',
        ]);
    }

    public function delete(string $key): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM site_settings WHERE `key` = :key');
        return $stmt->execute(['key' => $key]);
    }
}
