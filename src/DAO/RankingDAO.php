<?php
declare(strict_types=1);

namespace App\DAO;

use App\Core\Conexao;
use InvalidArgumentException;
use PDO;

// ranking nao tem tabela, e so uma consulta em cima das partidas
class RankingDAO
{
    private const SALAS = ['facil1', 'facil2', 'medio1', 'medio2', 'dificil1', 'dificil2'];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
    }

    // melhores tempos de uma sala
    public function top(string $sala, int $limite = 10): array
    {
        if (!in_array($sala, self::SALAS, true)) {
            throw new InvalidArgumentException('Sala inválida.');
        }

        // inner join pra pegar o nome do jogador. so entra partida concluida.
        // menor tempo primeiro, se empatar quem errou menos ganha
        $stmt = $this->pdo->prepare(
            "SELECT j.nome, p.tempo_segundos, p.erros
             FROM partidas p
             INNER JOIN jogadores j ON j.id = p.jogador_id
             WHERE p.sala = :sala AND p.status = 'concluida'
             ORDER BY p.tempo_segundos ASC, p.erros ASC
             LIMIT :limite"
        );

        // limit precisa de inteiro de verdade, por isso bindValue com PARAM_INT
        $stmt->bindValue(':sala', $sala);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}