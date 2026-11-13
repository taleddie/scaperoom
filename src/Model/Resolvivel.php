<?php
declare(strict_types=1);

namespace App\Model;

interface Resolvivel
{
    public function tentar(string $resposta): bool;
}
