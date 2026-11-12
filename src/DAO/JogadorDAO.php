<?php
declare(strict_types=1);

namespace App\DAO;

// "use" importa classes de outros namespaces para usar só o nome curto
use App\Core\Conexao;
use App\Model\Jogador;
use PDO;

class JogadorDAO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Conexao::getInstancia();
    }

    public function criar(string $nome, string $email, string $senha): Jogador
    {
        // password hash p deixar o usuario seguro rs
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // prepared statement p evitar aql injection.
        $stmt = $this->pdo->prepare(
            'INSERT INTO jogadores (nome, email, senha_hash)
             VALUES (:nome, :email, :hash)'
        );

        // preenche com os valores e roda
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':hash'  => $hash,
        ]);

        // retorna o id q o auto_increment gerou
        return new Jogador((int) $this->pdo->lastInsertId(), $nome, $email, $hash);
    }

    // formas de buscar jogador
    public function buscarPorEmail(string $email): ?Jogador
    {
        $stmt = $this->pdo->prepare('SELECT * FROM jogadores WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $linha = $stmt->fetch();

        // operador ternario
        return $linha ? $this->montar($linha) : null;
    }

    public function buscarPorId(int $id): ?Jogador
    {
        $stmt = $this->pdo->prepare('SELECT * FROM jogadores WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $linha = $stmt->fetch();

        return $linha ? $this->montar($linha) : null;
    }

    // recusa email repetido
    public function emailExiste(string $email): bool
    {
        // select 1, pois nao precisamos dos dados do usuario
        $stmt = $this->pdo->prepare('SELECT 1 FROM jogadores WHERE email = :email');
        $stmt->execute([':email' => $email]);

        // devolve 1 se achou, false se nao
        return (bool) $stmt->fetchColumn();
    }

    // LOGINNNNNNNNN

    // autenticacao - ve se email e senha batem
    public function autenticar(string $email, string $senha): ?Jogador
    {
        $jogador = $this->buscarPorEmail($email);

        // password_verify compara a senha digitada com o hash
        if ($jogador !== null && password_verify($senha, $jogador->getSenhaHash())) {
            return $jogador;
        }

        // null tanto pra se email nao existe, quanto pra senha errada
        return null;
    }

    // metodo pra montar objeto e nao repetir o "new Jogador()" varias vezes
    private function montar(array $linha): Jogador
    {
        return new Jogador(
            (int) $linha['id'],     // o banco devolve tudo como texto, convertemos
            $linha['nome'],
            $linha['email'],
            $linha['senha_hash']
        );
    }
}