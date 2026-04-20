<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Repositories\SiteSettingRepository;

class SiteController extends Controller
{
    private SiteSettingRepository $settingRepo;

    public function __construct()
    {
        $this->settingRepo = new SiteSettingRepository();
    }

    public function index()
    {
        $this->view('dashboard/website', [
            'title' => 'Website - ' . APP_NAME
        ]);
    }

    public function configuracoes()
    {
        $settings = $this->settingRepo->getAll();
        
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting['key']] = $setting['value'];
        }

        // Verificar se é uma requisição AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($isAjax) {
            // Retornar JSON para requisições AJAX
            header('Content-Type: application/json');
            echo json_encode(['settings' => $data]);
            return;
        }

        // Renderizar view para requisições normais
        $this->view('dashboard/configuracoes-site', [
            'title' => 'Configurações do Site - ' . APP_NAME,
            'settings' => $data,
        ]);
    }

    public function salvarConfiguracao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }

        // Ler JSON do corpo da requisição
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }

        try {
            // Salvar cada configuração
            foreach ($data as $key => $value) {
                // Converter checkboxes vazios (unchecked) em 0
                if ($value === 'on' || $value === '1') {
                    $value = '1';
                } elseif (empty($value) && isset($data[$key])) {
                    $value = '0';
                }
                
                $this->settingRepo->set($key, $value);
            }

            echo json_encode(['success' => true, 'message' => 'Configurações salvas com sucesso']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}