<?php
declare(strict_types=1);

namespace App\Core;

// tudo de sessao fica aqui
class Sessao
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // cookie da sessao: js nao le (httponly) e nao vai de outros sites (samesite)
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // login
    public static function logar(int $id, string $nome): void
    {
        // gera outro id de sessao a cada login, p evitar session fixation
        session_regenerate_id(true);
        $_SESSION['jogador_id']   = $id;
        $_SESSION['jogador_nome'] = $nome;
    }

    public static function estaLogado(): bool
    {
        return isset($_SESSION['jogador_id']);
    }

    public static function jogadorId(): ?int
    {
        // ?? = se nao existir, usa null
        return $_SESSION['jogador_id'] ?? null;
    }

    public static function jogadorNome(): string
    {
        return $_SESSION['jogador_nome'] ?? '';
    }

    // area restrita: chama no comeco de toda acao que precisa de login
    public static function exigirLogin(): void
    {
        if (!self::estaLogado()) {
            Roteador::redirecionar('index.php?acao=login');
        }
    }

    public static function sair(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    // estado do jogo (some quando sai, nao salva progresso)
    public static function iniciarJogo(int $partidaId, string $sala, array $segredos): void
    {
        $_SESSION['jogo'] = [
            'partida_id' => $partidaId,
            'sala'       => $sala,
            'codigo'     => $segredos['codigo'],
            'palavra'    => $segredos['palavra'],
            // etapas q ja foram feitas, o servidor confere antes de liberar a proxima
            'etapas'     => [
                'mesa'           => false,
                'armario'        => false,
                'armario_aberto' => false,
                'porta'          => false,
            ],
        ];
    }

    public static function jogo(): ?array
    {
        return $_SESSION['jogo'] ?? null;
    }

    public static function marcarEtapa(string $etapa): void
    {
        // so marca se a etapa existir
        if (isset($_SESSION['jogo']['etapas'][$etapa])) {
            $_SESSION['jogo']['etapas'][$etapa] = true;
        }
    }

    public static function limparJogo(): void
    {
        unset($_SESSION['jogo']);
    }

    // mensagem q aparece uma vez so, depois de ler ela some
    public static function mensagem(string $texto): void
    {
        $_SESSION['mensagem'] = $texto;
    }

    public static function pegarMensagem(): ?string
    {
        $texto = $_SESSION['mensagem'] ?? null;
        unset($_SESSION['mensagem']);
        return $texto;
    }

    // token csrf
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function csrfValido(): bool
    {
        $enviado = (string) filter_input(INPUT_POST, 'csrf');

        // hash_equals compara sem vazar nada pelo tempo
        return $enviado !== '' && hash_equals(self::csrfToken(), $enviado);
    }
}