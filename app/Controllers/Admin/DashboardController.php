<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Repositories\CarRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\VendaRepository;

class DashboardController extends Controller
{
    public function index()
    {
        $countCar = (new CarRepository())->getTheNumberOfVehicles();
        $countSeles = (new VendaRepository())->getTheNumbersSeles();
        $countClients = (new ClienteRepository())->getTheNumbersClients();
        
        // Dados para os gráficos
        $vendaRepo = new VendaRepository();
        $salesByMonth = $vendaRepo->getSalesByMonth(12);
        $topBrands = $vendaRepo->getTopBrands(5);
        
        // Mês/Ano atual ou selecionado
        $currentMonth = date('m');
        $currentYear = date('Y');
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : $currentMonth;
        $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;
        
        // Buscar vendas do mês selecionado agrupadas por dia
        $salesByDay = $vendaRepo->getSalesByDay($selectedYear, $selectedMonth);
        $lastDayWithSales = $vendaRepo->getLastDayWithSales($selectedYear, $selectedMonth);
        $recentSales = $vendaRepo->getRecentSales(4);

        echo $this->view('dashboard/index', [
            'countCar' => $countCar,
            'countSeles' => $countSeles,
            'countClients' => $countClients,
            'salesByMonth' => $salesByMonth,
            'topBrands' => $topBrands,
            'salesByDay' => $salesByDay,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'lastDayWithSales' => $lastDayWithSales,
            'recentSales' => $recentSales,
            'title' => 'Dashboard - ' . APP_NAME,
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
        ]);
    }

    // Endpoint AJAX para buscar vendas de um mês específico
    public function getSalesData()
    {
        header('Content-Type: application/json');
        
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        $vendaRepo = new VendaRepository();
        $salesByDay = $vendaRepo->getSalesByDay($year, $month);
        $lastDayWithSales = $vendaRepo->getLastDayWithSales($year, $month);
        
        echo json_encode([
            'sales' => $salesByDay,
            'lastDay' => $lastDayWithSales
        ]);
        exit;
    }

    public function configuracoes()
    {
        echo $this->view('dashboard/configuracoes', [
            'siteConfig' => 'Teste de configuração do site', // Exemplo de dado para a view
            'title' => 'Configurações - ' . APP_NAME,
        ]);
    }
}
