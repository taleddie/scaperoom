<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Roteador;
use App\Core\Sessao;

Sessao::iniciar();

// sem acao: logado vai pras salas, deslogado vai pro login
$padrao = Sessao::estaLogado() ? 'salas' : 'login';
$acao   = (string) (filter_input(INPUT_GET, 'acao') ?: $padrao);

Roteador::despachar($acao);
