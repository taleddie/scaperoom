<?php
declare(strict_types=1);

namespace App\Model;

class Jogador
{
    public function __construct(
        private ?int $id,
        private string $nome,
        private string $email,
        private string $senhaHash = ''
    ) {}

    // getters
    public function getId(): ?int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenhaHash(): string { return $this->senhaHash; }

    // set
    public function setId(int $id): void { $this->id = $id; }
}