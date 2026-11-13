<?php
declare(strict_types=1);

namespace App\Core;
/* classe pra organizar oq vai ser mostrado na tela (NÂO é a mesma coisa que a pasta de
mesmo nome, lá é onde ficam os arquivos de visualização)
                                                                        --- Jolea */
class View
{
    /* $view = nome do arquivo
       $dados = oq o controller vai passar pra tela
       $layout = se deve usar o layout (pode ser que eu tire se não usarmos um pré disposto pra 
       todas as salas)*/
    public static function renderizar(string $view, array $dados = [], bool $layout = true): void
    {
        // validação do nome da view (só passa letras, números, underline e barra)
        // para ninguém tentar acessar arquivos pela url
        if (!preg_match('#^[a-z0-9_/]+$#i', $view)) {
            throw new \InvalidArgumentException('Nome de view inválido.');
        }

        $arquivo = __DIR__ . '/../View/' . $view . '.php';

        // vê se o arquivo existe
        if (!is_file($arquivo)) {
            throw new \RuntimeException("View não encontrada: $view");
        }

        //separa as variáveis do array $dados em variáveis individuais, com o nome da chave
        // e o EXTR_SKIP evita sobrescrever variáveis que já existem
        extract($dados, EXTR_SKIP);

        ob_start();
        require $arquivo;
        $conteudo = (string) ob_get_clean();

        if ($layout) {
            require __DIR__ . '/../View/layout.php';
        } else {
            echo $conteudo;
        }
    }

    // função pra escapar caracteres especiais (pra evitar ataques XSS)
    public static function e(?string $texto): string
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
    }
}