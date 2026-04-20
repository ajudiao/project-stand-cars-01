<?php 

namespace App\Controllers\Admin;
use App\Core\Controller;

class PerfilController extends Controller
{
    public function index()
    {
        // Lógica para exibir o perfil do administrador
        $this->view('dashboard/perfil', [
            'title' => 'Perfil - ' . APP_NAME
        ]);
    }
}