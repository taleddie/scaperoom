<?php
declare(strict_types=1);

namespace App\Core;

use App\Controller\AuthController;
use App\Controller\JogoController;
use App\Controller\RankingController;

class Roteador
{
    // acao => [classe, metodo, metodo HTTP, exige login?]
    private const ROTAS = [
        'login'     => [AuthController::class,    'mostrarLogin',    'GET',  false],
        'logar'     => [AuthController::class,    'login',           'POST', false],
        'cadastro'  => [AuthController::class,    'mostrarCadastro', 'GET',  false],
        'cadastrar' => [AuthController::class,    'cadastrar',       'POST', false],
        'sair'      => [AuthController::class,    'sair',            'POST', true],
        'salas'           => [JogoController::class,    'salas',          'GET',  true],
        'iniciar'         => [JogoController::class,    'iniciar',        'POST', true],
        'sala'            => [JogoController::class,    'sala',           'GET',  true],
        'examinar'        => [JogoController::class,    'examinar',       'GET',  true],
        'tentar'          => [JogoController::class,    'tentar',         'POST', true],
        'verificar_tempo' => [JogoController::class,    'verificarTempo', 'GET',  true],
        'jumpscare'       => [JogoController::class,    'jumpscare',      'GET',  true],
        'vitoria'         => [JogoController::class,    'vitoria',        'GET',  true],
        'preferencias'    => [JogoController::class,    'preferencias',   'POST', true],
        'ranking'         => [RankingController::class, 'index',          'GET',  true]
    ];

    public static function despachar(string $acao): void
    {
        $rota = self::ROTAS[$acao] ?? null;
        if ($rota === null) {
            self::erro(404);
        }

        [$classe, $metodo, $http, $exigeLogin] = $rota;

        if ($_SERVER['REQUEST_METHOD'] !== $http) {
            self::erro(405);
        }
        if ($exigeLogin) {
            Sessao::exigirLogin();
        }
        if ($http === 'POST' && !Sessao::csrfValido()) {
            self::erro(403);
        }

        (new $classe())->$metodo();
    }

    public static function redirecionar(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    private static function erro(int $codigo): never
    {
        http_response_code($codigo);
        echo "Erro $codigo";
        exit;
    }
}