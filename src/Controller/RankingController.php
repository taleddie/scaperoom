<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\DAO\RankingDAO;
use App\Model\Sala;

// ranking por sala (so partidas concluidas)
class RankingController
{
    public function index(): void
    {
        // sala pedida na url. se for invalida, usa a primeira
        $pedida = (string) filter_input(INPUT_GET, 'sala');
        $atual  = Sala::existe($pedida) ? $pedida : Sala::ids()[0];

        View::renderizar('ranking', [
            'titulo'  => 'Ranking',
            'salas'   => Sala::ids(),
            'atual'   => $atual,
            'ranking' => (new RankingDAO())->top($atual, 10),
        ]);
    }
}
