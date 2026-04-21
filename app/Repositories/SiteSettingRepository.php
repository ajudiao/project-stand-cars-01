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
        $stmt = $this->conn->query('SELECT * FROM site_settings LIMIT 1');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];
        return $row ?: [];
    }

    public function get(string $key): ?string
    {
        $stmt = $this->conn->prepare('SELECT ' . $key . ' FROM site_settings LIMIT 1');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result[$key] ?? null;
    }

    public function update(array $data): bool
    {
        $sets = [];
        $params = [];
        foreach ($data as $key => $value) {
            $sets[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }
        $params['updated_at'] = date('Y-m-d H:i:s');

        $sql = 'UPDATE site_settings SET ' . implode(', ', $sets) . ', updated_at = :updated_at WHERE id = 1';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function createDefault(): bool
    {
        $sql = 'INSERT INTO site_settings (id) VALUES (1) ON DUPLICATE KEY UPDATE id = 1';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }
}
