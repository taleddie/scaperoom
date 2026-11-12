<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Conexao
{
    private static ?PDO $instancia = null;

    private function __construct() {}

    public static function getInstancia(): PDO
    {
        if (self::$instancia === null) {
            $cfg = require __DIR__ . '/../../config/database.php';

            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['banco']};charset={$cfg['charset']}";

            try {
                self::$instancia = new PDO($dsn, $cfg['usuario'], $cfg['senha'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                throw new \RuntimeException('Não foi possível conectar ao banco de dados.');
            }
        }

        return self::$instancia;
    }
}
