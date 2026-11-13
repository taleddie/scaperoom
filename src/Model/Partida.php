<?php
declare(strict_types=1);

namespace App\Model;

final class Partida
{
    public function __construct(
        private int $id,
        private int $jogadorId,
        private string $dificuldade,
        private string $sala,
        private int $limiteSegundos,
        private int $erros = 0,
        private int $dicasUsadas = 0,
        private string $status = 'em_andamento',
        private int $segundosDecorridos = 0,
        private ?int $tempoSegundos = null
    ) {
    }

    public static function fromArray(array $linha): self
    {
        return new self(
            (int) $linha['id'],
            (int) $linha['jogador_id'],
            (string) $linha['dificuldade'],
            (string) $linha['sala'],
            (int) $linha['limite_segundos'],
            (int) ($linha['erros'] ?? 0),
            (int) ($linha['dicas_usadas'] ?? 0),
            (string) ($linha['status'] ?? 'em_andamento'),
            (int) ($linha['segundos_decorridos'] ?? 0),
            isset($linha['tempo_segundos']) ? (int) $linha['tempo_segundos'] : null
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getJogadorId(): int
    {
        return $this->jogadorId;
    }

    public function getDificuldade(): string
    {
        return $this->dificuldade;
    }

    public function getSala(): string
    {
        return $this->sala;
    }

    public function getLimiteSegundos(): int
    {
        return $this->limiteSegundos;
    }

    public function getErros(): int
    {
        return $this->erros;
    }

    public function getDicasUsadas(): int
    {
        return $this->dicasUsadas;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSegundosDecorridos(): int
    {
        return $this->segundosDecorridos;
    }

    public function getTempoSegundos(): ?int
    {
        return $this->tempoSegundos;
    }

    public function penalidadeErro(): int
    {
        return Dificuldade::getConfig($this->dificuldade)['penalidade_erro'];
    }

    public function tempoRestante(): int
    {
        $restante = $this->limiteSegundos
            - $this->segundosDecorridos
            - ($this->erros * $this->penalidadeErro());

        return max(0, $restante);
    }

    public function tempoEsgotado(): bool
    {
        return $this->tempoRestante() <= 0;
    }

    public function emAndamento(): bool
    {
        return $this->status === 'em_andamento';
    }
}
