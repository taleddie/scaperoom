<?php
declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;
use LogicException;

final class Sala
{
    private const CATALOGO = [
        'facil1' => [
            'dificuldade'   => 'facil',
            'nome'          => 'Almoxarifado',
            'descricao'     => 'Prateleiras vazias e uma mesa coberta de poeira.',
            'codigo'        => '472',
            'palavra'       => null,
            'codigo_na_url' => false,
        ],
        'facil2' => [
            'dificuldade'   => 'facil',
            'nome'          => 'Sala de Espera',
            'descricao'     => 'Cadeiras enfileiradas e um relógio parado.',
            'codigo'        => '815',
            'palavra'       => null,
            'codigo_na_url' => false,
        ],
        'medio1' => [
            'dificuldade'   => 'medio',
            'nome'          => 'Arquivo',
            'descricao'     => 'Fichários antigos escondem mais do que papéis.',
            'codigo'        => '3917',
            'palavra'       => 'FORMULA',
            'codigo_na_url' => true,
        ],
        'medio2' => [
            'dificuldade'   => 'medio',
            'nome'          => 'Depósito',
            'descricao'     => 'Frascos sem rótulo e um armário trancado.',
            'codigo'        => '6204',
            'palavra'       => 'REAGENTE',
            'codigo_na_url' => true,
        ],
        'dificil1' => [
            'dificuldade'   => 'dificil',
            'nome'          => 'Laboratório',
            'descricao'     => 'Luzes piscando e o cheiro de algo queimando.',
            'codigo'        => '58306',
            'palavra'       => 'CATALISADOR',
            'codigo_na_url' => false,
        ],
        'dificil2' => [
            'dificuldade'   => 'dificil',
            'nome'          => 'Sala de Observação',
            'descricao'     => 'Um vidro espesso separa você do que há lá dentro.',
            'codigo'        => '90417',
            'palavra'       => 'MICROSCOPIO',
            'codigo_na_url' => false,
        ],
    ];

    public static function existe(string $sala): bool
    {
        return isset(self::CATALOGO[$sala]);
    }

    public static function ids(): array
    {
        return array_keys(self::CATALOGO);
    }

    public static function porDificuldade(string $dificuldade): array
    {
        Dificuldade::getConfig($dificuldade);

        return array_values(array_filter(
            self::ids(),
            static fn (string $sala): bool => self::CATALOGO[$sala]['dificuldade'] === $dificuldade
        ));
    }

    public static function dados(string $sala): array
    {
        $dados = self::obter($sala);

        return [
            'id'            => $sala,
            'dificuldade'   => $dados['dificuldade'],
            'nome'          => $dados['nome'],
            'descricao'     => $dados['descricao'],
            'codigo_na_url' => $dados['codigo_na_url'],
            'tem_mesa'      => !$dados['codigo_na_url'],
            'tem_armario'   => $dados['palavra'] !== null,
        ];
    }

    public static function dificuldade(string $sala): string
    {
        return self::obter($sala)['dificuldade'];
    }

    public static function nome(string $sala): string
    {
        return self::obter($sala)['nome'];
    }

    public static function codigo(string $sala): string
    {
        return self::obter($sala)['codigo'];
    }

    public static function palavra(string $sala): ?string
    {
        return self::obter($sala)['palavra'];
    }

    public static function codigoNaUrl(string $sala): bool
    {
        return self::obter($sala)['codigo_na_url'];
    }

    public static function temMesa(string $sala): bool
    {
        return !self::codigoNaUrl($sala);
    }

    public static function temArmario(string $sala): bool
    {
        return self::palavra($sala) !== null;
    }

    public static function enigmaArmario(string $sala): EnigmaCodigo
    {
        if (!self::temArmario($sala)) {
            throw new LogicException('Esta sala não tem armário.');
        }

        return new EnigmaCodigo(self::codigo($sala));
    }

    public static function enigmaPorta(string $sala): EnigmaCodigo
    {
        return new EnigmaCodigo(self::palavra($sala) ?? self::codigo($sala));
    }

    private static function obter(string $sala): array
    {
        return self::CATALOGO[$sala] ?? throw new InvalidArgumentException('Sala inválida.');
    }
}
