<?php
declare(strict_types=1);

namespace App\DAO;

use App\Core\Conexao;
use InvalidArgumentException;
use PDO;

class PartidaDAO
{
    // valores q podem, confere antes de ir pro banco
    private const DIFICULDADES  = ['facil', 'medio', 'dificil'];
    private const SALAS         = ['facil1', 'facil2', 'medio1', 'medio2', 'dificil1', 'dificil2'];
    private const STATUS_FINAIS = ['concluida', 'tempo_esgotado', 'abandonada'];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
    }

    // cria uma partida nova e devolve o id dela
    public function criar(int $jogadorId, string $sala, string $dificuldade, int $limiteSegundos): int
    {
        // in_array com true no final compara valor e tipo
        if (!in_array($sala, self::SALAS, true)) {
            throw new InvalidArgumentException('Sala inválida.');
        }
        if (!in_array($dificuldade, self::DIFICULDADES, true)) {
            throw new InvalidArgumentException('Dificuldade inválida.');
        }

        // perde partida ativa se sair do jogo
        $this->abandonarAtivas($jogadorId);

        // o inicio quem preenche e o banco, com a hora do servidor
        $stmt = $this->pdo->prepare(
            'INSERT INTO partidas (jogador_id, dificuldade, sala, limite_segundos)
             VALUES (:jogador, :dificuldade, :sala, :limite)'
        );
        $stmt->execute([
            ':jogador'     => $jogadorId,
            ':dificuldade' => $dificuldade,
            ':sala'        => $sala,
            ':limite'      => $limiteSegundos,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // ler a partida em andamento, ou null se nao tiver
    public function buscarAtiva(int $jogadorId): ?array
    {
        // TIMESTAMPDIFF calcula dentro do mysql os segundos de jogo
        $stmt = $this->pdo->prepare(
            "SELECT *, TIMESTAMPDIFF(SECOND, inicio, NOW()) AS segundos_decorridos
             FROM partidas
             WHERE jogador_id = :jogador AND status = 'em_andamento'
             ORDER BY id DESC
             LIMIT 1"
        );
        $stmt->execute([':jogador' => $jogadorId]);

        return $stmt->fetch() ?: null;
    }

    public function buscarPorId(int $partidaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *, TIMESTAMPDIFF(SECOND, inicio, NOW()) AS segundos_decorridos
             FROM partidas WHERE id = :id'
        );
        $stmt->execute([':id' => $partidaId]);

        return $stmt->fetch() ?: null;
    }

    // soma 1 erro, so se a partida ainda estiver em andamento
    public function registrarErro(int $partidaId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE partidas SET erros = erros + 1
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':id' => $partidaId]);
    }

    public function registrarDica(int $partidaId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE partidas SET dicas_usadas = dicas_usadas + 1
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':id' => $partidaId]);
    }

    // termina a partida. true se terminou agora, false se ja tinha terminado
    public function finalizar(int $partidaId, string $status): bool
    {
        if (!in_array($status, self::STATUS_FINAIS, true)) {
            throw new InvalidArgumentException('Status final inválido.');
        }

        // tempo total calculado no banco (fim - inicio), nunca vem do navegador.
        // o where garante q so finaliza uma vez
        $stmt = $this->pdo->prepare(
            "UPDATE partidas
             SET status = :status,
                 fim = NOW(),
                 tempo_segundos = TIMESTAMPDIFF(SECOND, inicio, NOW())
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':status' => $status, ':id' => $partidaId]);

        // rowCount = quantas linhas o update mudou
        return $stmt->rowCount() > 0;
    }

    // marca como abandonada a partida em andamento do jogador
    public function abandonarAtivas(int $jogadorId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE partidas
             SET status = 'abandonada',
                 fim = NOW(),
                 tempo_segundos = TIMESTAMPDIFF(SECOND, inicio, NOW())
             WHERE jogador_id = :jogador AND status = 'em_andamento'"
        );
        $stmt->execute([':jogador' => $jogadorId]);
    }

    // salas q o jogador ja concluiu, pra marcar na tela de escolha
    public function salasConcluidas(int $jogadorId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT sala FROM partidas
             WHERE jogador_id = :jogador AND status = 'concluida'"
        );
        $stmt->execute([':jogador' => $jogadorId]);

        // devolve so a coluna: ['facil1', 'medio2', ...]
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}