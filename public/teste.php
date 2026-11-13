<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
require __DIR__ . '/../vendor/autoload.php';

use App\DAO\JogadorDAO;
use App\DAO\PartidaDAO;
use App\DAO\RankingDAO;

$jogadores = new JogadorDAO();
$partidas  = new PartidaDAO();
$ranking   = new RankingDAO();

// Cria o jogador de teste se ainda não existir
if (!$jogadores->emailExiste('teste@teste.com')) {
    $jogadores->criar('Teste', 'teste@teste.com', 'senha123');
}
$jogador = $jogadores->buscarPorEmail('teste@teste.com');

$id = $partidas->criar($jogador->getId(), 'facil1', 'facil', 900);
echo "Partida criada, id $id<br>";

$ativa = $partidas->buscarAtiva($jogador->getId());
echo "Ativa: sala={$ativa['sala']}, status={$ativa['status']}<br>";

$partidas->registrarErro($id);
echo "Erros: " . $partidas->buscarPorId($id)['erros'] . " (esperado 1)<br>";

sleep(2);
echo $partidas->finalizar($id, 'concluida') ? "Finalizou: OK<br>" : "Finalizou: FALHOU<br>";
echo !$partidas->finalizar($id, 'tempo_esgotado') ? "Segunda finalização bloqueada: OK<br>" : "Segunda finalização: FALHOU<br>";

$a = $partidas->criar($jogador->getId(), 'medio1', 'medio', 600);
$b = $partidas->criar($jogador->getId(), 'medio1', 'medio', 600);
echo $partidas->buscarPorId($a)['status'] === 'abandonada' ? "Partida anterior abandonada: OK<br>" : "Abandono: FALHOU<br>";
echo $partidas->buscarPorId($b)['status'] === 'em_andamento' ? "Nova partida ativa: OK<br>" : "Nova ativa: FALHOU<br>";

echo "<pre>";
print_r($ranking->top('facil1', 5));
echo "</pre>";
