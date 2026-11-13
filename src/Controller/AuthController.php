<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Roteador;
use App\Core\Sessao;
use App\Core\View;
use App\DAO\JogadorDAO;

// cadastro, login e logout
class AuthController
{
    public function mostrarLogin(): void
    {
        View::renderizar('login', ['titulo' => 'Entrar']);
    }

    public function login(): void
    {
        // FILTER_VALIDATE_EMAIL devolve o email se valido, false se invalido, null se nao veio
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = (string) filter_input(INPUT_POST, 'senha');

        if (!$email || $senha === '') {
            View::renderizar('login', ['titulo' => 'Entrar', 'erro' => 'Preencha e-mail e senha corretamente.']);
            return;
        }

        $jogador = (new JogadorDAO())->autenticar($email, $senha);

        if ($jogador === null) {
            // mensagem generica, nao diz se errou o email ou a senha
            View::renderizar('login', ['titulo' => 'Entrar', 'erro' => 'E-mail ou senha incorretos.']);
            return;
        }

        Sessao::logar($jogador->getId(), $jogador->getNome());
        Roteador::redirecionar('index.php?acao=salas');
    }

    public function mostrarCadastro(): void
    {
        View::renderizar('cadastro', ['titulo' => 'Cadastro']);
    }

    public function cadastrar(): void
    {
        $nome  = trim((string) filter_input(INPUT_POST, 'nome'));
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = (string) filter_input(INPUT_POST, 'senha');

        $erro = null;

        // mb_strlen conta certo mesmo com acento
        if (mb_strlen($nome) < 2 || mb_strlen($nome) > 80) {
            $erro = 'O nome deve ter entre 2 e 80 caracteres.';
        } elseif (!$email) {
            $erro = 'Informe um e-mail válido.';
        } elseif (mb_strlen($senha) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        }

        $dao = new JogadorDAO();

        // recusa email repetido
        if ($erro === null && $dao->emailExiste($email)) {
            $erro = 'Este e-mail já está cadastrado.';
        }

        if ($erro !== null) {
            // volta pro formulario com o erro e o q ja tinha digitado (menos a senha)
            View::renderizar('cadastro', [
                'titulo' => 'Cadastro',
                'erro'   => $erro,
                'nome'   => $nome,
                'email'  => $email ?: '',
            ]);
            return;
        }

        // deu certo, grava e ja deixa logado
        $jogador = $dao->criar($nome, $email, $senha);
        Sessao::logar($jogador->getId(), $jogador->getNome());
        Roteador::redirecionar('index.php?acao=salas');
    }

    public function sair(): void
    {
        Sessao::sair();
        Roteador::redirecionar('index.php?acao=login');
    }
}