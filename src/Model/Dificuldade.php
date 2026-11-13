<?php
namespace App\Model;

class Dificuldade {
    public static function getConfig(string $nivel): array {
        return match ($nivel) {
            'facil' => [
                'limite_segundos' => 900, 
                'penalidade_erro' => 0,
                'digitos' => 3
            ],
            'medio' => [
                'limite_segundos' => 600, 
                'penalidade_erro' => 15,  
                'digitos' => 4
            ],
            'dificil' => [
                'limite_segundos' => 360, 
                'penalidade_erro' => 30, 
                'digitos' => 5
            ],
            default => throw new \InvalidArgumentException("Nível de dificuldade inválido.")
        };
    }
}
?>