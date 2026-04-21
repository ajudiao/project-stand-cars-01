<?php

use App\Core\View;
use Pecee\SimpleRouter\SimpleRouter as Router;
use App\Middleware\AdminMiddleware;
use Pecee\Http\Request;
use Pecee\SimpleRouter\Route\Route;

//
// ------------------------
// SITE PÚBLICO
// ------------------------
//
Router::group([
    'namespace' => 'App\Controllers\Public'
], function () {

    Router::get('/', 'HomeController@index');
    

    Router::get('/carros', 'CarrosController@index');
    Router::get('/veiculos', 'VeiculosController@index');
    Router::get('/veiculos/{id}', 'VeiculosController@show');

    Router::get('/contato', 'ContatoController@index');
    Router::get('/recomendacao', 'RecomendacaoController@index');
    Router::get('/sobre', 'SobreController@index');
    Router::get('/servicos', 'ServicosController@index');

    Router::get('/blog', 'BlogController@index');

    Router::get('/noticias', 'NoticiasController@index');
    Router::get('/noticias/{id}', 'NoticiasController@show');
});

//
// ------------------------
// LOGIN ADMIN
// ------------------------
//
Router::group([
    'prefix' => '/admin',
    'namespace' => 'App\Controllers\Admin'
], function () {

    Router::get('/login', 'AuthController@loginForm');
    Router::post('/login', 'AuthController@login');
});

//
// ------------------------
// PAINEL ADMIN (PROTEGIDO)
// ------------------------
//
Router::group([
    'prefix' => '/admin',
    'namespace' => 'App\Controllers\Admin',
    'middleware' => AdminMiddleware::class
], function () {

    // Dashboard
    Router::get('/', 'DashboardController@index');
    Router::get('/dashboard/get-sales-data', 'DashboardController@getSalesData');
    Router::get('/notificacoes', 'NotificationsController@index');

    //
    // VEÍCULOS (REST)
    //
    Router::get('automoveis', 'AutomoveisController@index');          // listar
    Router::post('/automoveis/create', 'AutomoveisController@create');  // form (opcional)
    Router::post('/automoveis/', 'AutomoveisController@store');         // salvar
    Router::get('/automoveis/show/{id}', 'AutomoveisController@show');      // detalhes
    Router::get('/automoveis/{id}/edit', 'AutomoveisController@edit'); // editar form
    Router::put('/automoveis/{id}', 'AutomoveisController@update');    // atualizar
    Router::get('/automoveis/delete/{id}', 'AutomoveisController@delete'); // deletar
    Router::get('/automoveis/busca/', 'AutomoveisController@buscar');
    Router::post('/automoveis/update/{id}', 'AutomoveisController@update');

    //
    // CLIENTES (REST)
    //
    Router::get('/clientes', 'ClientesController@index');
    Router::post('/clientes', 'ClientesController@store');
    Router::get('/clientes/{id}/edit', 'ClientesController@edit');
    Router::get('/clientes/show/{id}', 'ClientesController@show');
    Router::put('/clientes/{id}', 'ClientesController@update');
    Router::delete('/clientes/{id}', 'ClientesController@delete');
    Router::get('/clientes/busca/', 'ClientesController@buscar');
    Router::get('/clientes/delete/{id}', 'ClientesController@delete');
    Router::post('/clientes/update/{id}', 'ClientesController@update');

    //
    // NOTÍCIAS (REST)
    //
    Router::get('/noticias', 'NoticiasController@index');
    Router::get('/noticias/create', 'NoticiasController@create');
    Router::post('/noticias', 'NoticiasController@store');
    Router::get('/noticias/{id}/edit', 'NoticiasController@edit');
    Router::put('/noticias/{id}', 'NoticiasController@update');
    Router::delete('/noticias/{id}', 'NoticiasController@delete');

    //
    // OUTROS
    //
    Router::get('/vendas', 'VendasController@index');
    Router::post('/vendas', 'VendasController@store');
    Router::get('/relatorios', 'RelatoriosController@index');
    Router::get('/relatorios/generate', 'RelatoriosController@generate');
    Router::post('/relatorios/custom-report', 'RelatoriosController@customReport');
    Router::get('/vendas/busca/', 'VendasController@buscar');
    Router::get('/vendas/delete/{id}', 'VendasController@delete');
    Router::get('/vendas/show/{id}', 'VendasController@show');
    Router::get('/vendas/{id}/recibo', 'VendasController@receipt');
    Router::post('/vendas/update/{id}', 'VendasController@update');

    //
    // USUÁRIOS (REST)
    //
    Router::get('/usuarios', 'UsuariosController@index');
    Router::post('/usuarios', 'UsuariosController@store');
    Router::post('/usuarios/update/{id}', 'UsuariosController@update');
    Router::get('/usuarios/delete/{id}', 'UsuariosController@delete');

    Router::get('/configuracoes', 'DashboardController@configuracoes');
    Router::post('/configuracoes/backup', 'DashboardController@backup');
    Router::get('/configuracoes/export', 'DashboardController@exportCsv');
    Router::get('/website/configuracoes', 'SiteController@configuracoes');
    Router::post('/website/configuracoes/salvar', 'SiteController@salvarConfiguracao');
    Router::get('/perfil', 'PerfilController@index');
    Router::post('/perfil/foto', 'PerfilController@updatePhoto');
    Router::post('/perfil/senha', 'PerfilController@changePassword');

    //
    // RELATÓRIOS
    //
    Router::get('/relatorios', 'RelatoriosController@index');
    Router::get('/relatorios/generate', 'RelatoriosController@generate');
    Router::post('/relatorios/custom-report', 'RelatoriosController@customReport');

    Router::get('/logout', 'AuthController@logout');
});

//
// ------------------------
// ERROS
// ------------------------
//
Router::error(function (Request $request, \Throwable $exception) {

    // Sempre loga o erro (boa prática)
    error_log($exception->getMessage());

    // MODO DESENVOLVIMENTO
    if (DEBUG) {

        http_response_code(500);

        echo "<h1>Erro:</h1>";
        echo "<p><strong>Mensagem:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Arquivo:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Linha:</strong> " . $exception->getLine() . "</p>";

        echo "<pre>";
        print_r($exception->getTrace());
        echo "</pre>";

        return;
    }

    // PRODUÇÃO (usuário nunca vê erro técnico)
    if ($exception->getCode() === 404) {
        http_response_code(404);

        View::render('errors/404', [
            'message' => 'Página não encontrada.'
        ]);
    } else {
        http_response_code(500);

        View::render('errors/500', [
            'message' => 'Erro interno. Tente novamente mais tarde.'
        ]);
    }
});