<?php
declare(strict_types=1);

namespace App\Model;

use App\Exception\CodigoIncorretoException;

final class EnigmaCodigo implements Resolvivel
{
    public function __construct(private string $resposta)
    {
    }

    public function tentar(string $resposta): bool
    {
        if ($this->normalizar($resposta) !== $this->normalizar($this->resposta)) {
            throw new CodigoIncorretoException('Resposta incorreta.');
        }

        return true;
    }

    private function normalizar(string $valor): string
    {
        $valor = trim($valor);
        $valor = function_exists('mb_strtoupper') ? mb_strtoupper($valor) : strtoupper($valor);

        return (string) preg_replace('/\s+/', '', $valor);
    }
}
