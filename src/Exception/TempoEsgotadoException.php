<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

// lancada qd o tempo acaba. guarda o id da partida p o controller conseguir fechar ela
class TempoEsgotadoException extends Exception
{
    public function __construct(private int $partidaId)
    {
        parent::__construct('O tempo acabou.');
    }

    public function getPartidaId(): int
    {
        return $this->partidaId;
    }
}
