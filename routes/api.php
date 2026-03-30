<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\RevisaoController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\CorController;
use Illuminate\Support\Facades\Route;

// Rotas públicas (sem autenticação)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/usuarios', [UsuarioController::class, 'store']); // cadastro

// Rotas protegidas (precisam de token)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    Route::get('/cores',   [CorController::class,   'index']);
    Route::post('/cores',  [CorController::class,   'store']);

    // Usuários (só admin)
    Route::apiResource('usuarios', UsuarioController::class)
         ->except(['store']);

    // Veículos
    Route::apiResource('veiculos', VeiculoController::class);

    // Revisões
    Route::apiResource('revisoes', RevisaoController::class);
    Route::post('/revisoes/disparar-lembretes', [RevisaoController::class, 'dispararLembretes']);

    // Relatórios (só admin)
    Route::prefix('relatorios')->group(function () {
        Route::get('/veiculos/todos',            [RelatorioController::class, 'todosVeiculos']);
        Route::get('/veiculos/por-pessoa',       [RelatorioController::class, 'veiculosPorPessoa']);
        Route::get('/veiculos/genero',           [RelatorioController::class, 'veiculosPorGenero']);
        Route::get('/veiculos/marcas',           [RelatorioController::class, 'marcasOrdenadas']);
        Route::get('/veiculos/marcas-genero',    [RelatorioController::class, 'marcasPorGenero']);
        Route::get('/pessoas/todas',             [RelatorioController::class, 'todasPessoas']);
        Route::get('/pessoas/genero',            [RelatorioController::class, 'pessoasPorGenero']);
        Route::get('/revisoes/periodo',          [RelatorioController::class, 'revisoesPorPeriodo']);
        Route::get('/revisoes/marcas',           [RelatorioController::class, 'marcasComMaisRevisoes']);
        Route::get('/revisoes/pessoas',          [RelatorioController::class, 'pessoasComMaisRevisoes']);
        Route::get('/revisoes/media-tempo',      [RelatorioController::class, 'mediaTempoRevisoes']);
        Route::get('/revisoes/proximas',         [RelatorioController::class, 'proximasRevisoes']);
        Route::get('/verificar/cpf/{cpf}',       [UsuarioController::class, 'verificarCpf']);
    });
});