<?php
// Mostra os erros na tela (só para desenvolver; em produção, desligar)
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use App\DAO\JogadorDAO;

$dao = new JogadorDAO();

$email = 'teste@teste.com';

if (!$dao->emailExiste($email)) {
    $dao->criar('Teste', $email, 'senha123');
    echo "Jogador criado.<br>";
}

// senha certa deve entrar (retorna um objeto que é "verdadeiro")
echo $dao->autenticar($email, 'senha123') ? "Login certo: OK<br>" : "Login certo: FALHOU<br>";

// senha errada deve ser rejeitada (retorna null)
echo $dao->autenticar($email, 'errada') === null ? "Senha errada rejeitada: OK<br>" : "Senha errada: FALHOU<br>";