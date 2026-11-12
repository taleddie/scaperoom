<?php
declare(strict_types=1);

namespace App\DAO;

use App\Core\Conexao;
use InvalidArgumentException;
use PDO;

class RankingDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
    }

    // Os melhores tempos de UMA dificuldade (não faz sentido comparar
    // 6 minutos do difícil com 15 do fácil)
    public function top(string $dificuldade, int $limite = 10): array
    {
        if (!in_array($dificuldade, ['facil', 'medio', 'dificil'], true)) {
            throw new InvalidArgumentException('Dificuldade inválida.');
        }

        // INNER JOIN junta duas tabelas: para cada partida, traz o nome
        // do jogador dono dela. Só entram partidas concluídas.
        // ORDER BY: menor tempo primeiro; empate = menos erros ganha.
        // O INDEX idx_ranking que criamos no banco acelera esta consulta.
        $stmt = $this->pdo->prepare(
            "SELECT j.nome, p.tempo_segundos, p.erros
             FROM partidas p
             INNER JOIN jogadores j ON j.id = p.jogador_id
             WHERE p.dificuldade = :dificuldade AND p.status = 'concluida'
             ORDER BY p.tempo_segundos ASC, p.erros ASC
             LIMIT :limite"
        );

        // LIMIT precisa receber um número inteiro de verdade. Por isso
        // usamos bindValue com PDO::PARAM_INT, em vez de passar no execute()
        // (que enviaria o valor como texto e daria erro de SQL).
        $stmt->bindValue(':dificuldade', $dificuldade);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        // fetchAll() devolve TODAS as linhas (um array de arrays)
        return $stmt->fetchAll();
    }
}