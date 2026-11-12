<?php
declare(strict_types=1);

namespace App\DAO;

use App\Core\Conexao;
use InvalidArgumentException;
use PDO;

class PartidaDAO
{
    private const DIFICULDADES  = ['facil', 'medio', 'dificil'];
    private const STATUS_FINAIS = ['concluida', 'tempo_esgotado', 'abandonada'];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
    }

    // cria uma partida nova e devolve o id dela
    public function criar(int $jogadorId, string $dificuldade, int $limiteSegundos): int
    {
        if (!in_array($dificuldade, self::DIFICULDADES, true)) {
            throw new InvalidArgumentException('Dificuldade inválida.');
        }

        // perde partida ativa se sair do jogo
        $this->abandonarAtivas($jogadorId);

        // inicio dado pelo banco com a hora do servidor
        $stmt = $this->pdo->prepare(
            'INSERT INTO partidas (jogador_id, dificuldade, limite_segundos)
             VALUES (:jogador, :dificuldade, :limite)'
        );
        $stmt->execute([
            ':jogador'     => $jogadorId,
            ':dificuldade' => $dificuldade,
            ':limite'      => $limiteSegundos,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    // partida em andamento do jogador, ou null se não tiver
    public function buscarAtiva(int $jogadorId): ?array
    {
        // TIMESTAMPDIFF calcula no mysql os segundos desde o inicio
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

    // busca uma partida pelo id
    public function buscarPorId(int $partidaId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *, TIMESTAMPDIFF(SECOND, inicio, NOW()) AS segundos_decorridos
             FROM partidas WHERE id = :id'
        );
        $stmt->execute([':id' => $partidaId]);

        return $stmt->fetch() ?: null;
    }

    // soma 1 erro (só se a partida ainda estiver em andamento)
    public function registrarErro(int $partidaId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE partidas SET erros = erros + 1
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':id' => $partidaId]);
    }

    // soma 1 dica usada
    public function registrarDica(int $partidaId): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE partidas SET dicas_usadas = dicas_usadas + 1
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':id' => $partidaId]);
    }

    // termina a partida
    public function finalizar(int $partidaId, string $status): bool
    {
        if (!in_array($status, self::STATUS_FINAIS, true)) {
            throw new InvalidArgumentException('Status final inválido.');
        }


        $stmt = $this->pdo->prepare(
            "UPDATE partidas
             SET status = :status,
                 fim = NOW(),
                 tempo_segundos = TIMESTAMPDIFF(SECOND, inicio, NOW())
             WHERE id = :id AND status = 'em_andamento'"
        );
        $stmt->execute([':status' => $status, ':id' => $partidaId]);

        return $stmt->rowCount() > 0;
    }

    // marca partidas abandonadas
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
}