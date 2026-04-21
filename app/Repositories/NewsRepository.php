<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class NewsRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function getAllPublished(): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM noticias WHERE status = ? ORDER BY data_publicacao DESC');
        $stmt->execute(['publicado']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBySlug(string $slug): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM noticias WHERE slug = ? AND status = ?');
        $stmt->execute([$slug, 'publicado']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getRecent(int $limit = 5): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM noticias WHERE status = ? ORDER BY data_publicacao DESC LIMIT ?');
        $stmt->execute(['publicado', $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}