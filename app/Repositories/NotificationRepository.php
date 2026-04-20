<?php

namespace App\Repositories;

use App\Core\Database;
use DateTime;
use PDO;

class NotificationRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    public function getNotifications(int $limit = 15): array
    {
        $notifications = array_merge(
            $this->getRecentSales($limit),
            $this->getRecentClients($limit),
            $this->getRecentVehicles($limit)
        );

        usort($notifications, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return array_slice($notifications, 0, $limit);
    }

    public function getWeeklySummary(): array
    {
        $salesCount = $this->conn->query(
            "SELECT COUNT(*) FROM vendas WHERE status = 'Concluido' AND data_venda >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        $clientsCount = $this->conn->query(
            "SELECT COUNT(*) FROM clientes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        $vehiclesCount = $this->conn->query(
            "SELECT COUNT(*) FROM veiculos WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        return [
            'sales' => (int) $salesCount,
            'clients' => (int) $clientsCount,
            'vehicles' => (int) $vehiclesCount,
        ];
    }

    private function getRecentSales(int $limit): array
    {
        $sql = "SELECT 
                    ven.id,
                    ven.data_venda,
                    ven.valor_pago,
                    ve.modelo,
                    cl.nome_completo AS cliente
                FROM vendas ven
                LEFT JOIN clientes cl ON ven.id_cliente = cl.id
                LEFT JOIN veiculos ve ON ven.id_veiculo = ve.id
                WHERE ven.status = 'Concluido'
                ORDER BY ven.data_venda DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'sale',
                'title' => 'Venda concluída',
                'message' => sprintf('%s comprou %s por R$ %s', $row['cliente'] ?? 'Cliente', $row['modelo'] ?? 'Veículo', number_format((float) $row['valor_pago'], 2, ',', '.')),
                'created_at' => $row['data_venda'],
                'timeAgo' => $this->formatTimeAgo($row['data_venda']),
                'url' => '/admin/vendas/show/' . $row['id'],
                'icon' => 'bi bi-check-circle',
                'badge' => 'success',
            ];
        }

        return $notifications;
    }

    private function getRecentClients(int $limit): array
    {
        $sql = "SELECT id, nome_completo, created_at FROM clientes ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'client',
                'title' => 'Novo cliente cadastrado',
                'message' => $row['nome_completo'],
                'created_at' => $row['created_at'],
                'timeAgo' => $this->formatTimeAgo($row['created_at']),
                'url' => '/admin/clientes/show/' . $row['id'],
                'icon' => 'bi bi-people-fill',
                'badge' => 'info',
            ];
        }

        return $notifications;
    }

    private function getRecentVehicles(int $limit): array
    {
        $sql = "SELECT ve.id, ve.modelo, ve.ano, m.nome AS marca, ve.created_at 
                FROM veiculos ve
                LEFT JOIN marcas m ON ve.id_marca = m.id
                ORDER BY ve.created_at DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $notifications = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'type' => 'vehicle',
                'title' => 'Novo automóvel adicionado',
                'message' => sprintf('%s %s (%s)', $row['marca'] ?? 'Marca', $row['modelo'] ?? 'Modelo', $row['ano'] ?? ''),
                'created_at' => $row['created_at'],
                'timeAgo' => $this->formatTimeAgo($row['created_at']),
                'url' => '/admin/automoveis/show/' . $row['id'],
                'icon' => 'bi bi-car-front',
                'badge' => 'primary',
            ];
        }

        return $notifications;
    }

    private function formatTimeAgo(string $datetime): string
    {
        try {
            $date = new DateTime($datetime);
        } catch (\Exception $e) {
            return 'agora mesmo';
        }

        $now = new DateTime();
        $diff = $date->diff($now);

        if ($diff->d > 0) {
            return 'há ' . $diff->d . ' dia' . ($diff->d > 1 ? 's' : '');
        }

        if ($diff->h > 0) {
            return 'há ' . $diff->h . ' hora' . ($diff->h > 1 ? 's' : '');
        }

        if ($diff->i > 0) {
            return 'há ' . $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '');
        }

        return 'há poucos segundos';
    }
}
