 <div align="center">

# ESCAPE ROOM

**Projeto de desenvolvimento web em PHP**

</div>

---

## Sobre o projeto

O **Escape Room** é um jogo de enigmas em formato de escape room, desenvolvido para navegador. A proposta é fazer com que o jogador investigue os cenários, encontre pistas e resolva desafios para conseguir escapar antes que o tempo termine.

A ambientação é inspirada nas **Backrooms**, especialmente na sensação de espaços vazios, repetitivos e desconfortáveis, e no **Notpron**, jogo conhecido por seus enigmas que exigem observação, interpretação e raciocínio fora do convencional.

O projeto foi desenvolvido em PHP e utiliza conceitos de programação orientada a objetos, arquitetura MVC, persistência de dados e validação de requisições.

## Estrutura de diretórios

```text
scaperoom/
│
├── config/
│   ├── database.example.php
│   └── database.php
│
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── estilo.css
│   │   ├── img/
│   │   │   └── m2/
│   │   └── js/
│   │       └── cronometro.js
│   ├── index.php
│   └── login.php
│
├── src/
│   ├── Controller/
│   ├── Core/
│   ├── DAO/
│   ├── Exception/
│   ├── Model/
│   ├── Salas/
│   └── View/
│       └── salas/
│
├── database.sql
├── composer.json
└── README.md
```

## Organização do projeto

A aplicação está organizada em diretórios que separam as responsabilidades do sistema. A estrutura segue o padrão **MVC (Model-View-Controller)**, complementado por classes de acesso a dados (DAO) e componentes centrais da aplicação.

### `config/`

Armazena os arquivos de configuração da conexão com o banco de dados. O arquivo `database.example.php` serve como modelo para a configuração local.

### `public/`

Contém os arquivos de entrada acessíveis pelo navegador e os recursos utilizados na interface:

- `assets/css/`: folhas de estilo;
- `assets/img/`: imagens utilizadas nos cenários;
- `assets/js/`: scripts de interação, como o cronômetro;
- `index.php`: ponto de entrada principal da aplicação;
- `login.php`: página de entrada utilizada na versão inicial do projeto.

### `src/Controller/`

Reúne os controladores responsáveis por receber as requisições e coordenar as ações do sistema. Inclui os fluxos de autenticação, jogo e ranking.

### `src/Core/`

Contém componentes fundamentais para o funcionamento da aplicação, como roteador, gerenciamento de sessão, conexão com o banco e renderização das páginas.

### `src/DAO/`

Centraliza o acesso e a manipulação dos dados persistidos. As classes DAO realizam operações relacionadas a jogadores, partidas e consultas do ranking.

### `src/Exception/`

Armazena exceções específicas do jogo, como tentativas com códigos incorretos e situações em que o tempo da partida se esgota.

### `src/Model/`

Contém as entidades e regras de negócio da aplicação, incluindo jogador, partida, sala, dificuldade e resolução de enigmas.

### `src/Salas/` e `src/View/`

`Salas/` reúne a lógica específica dos cenários. `View/` contém as páginas apresentadas ao jogador, como cadastro, login, seleção de salas, ranking, vitória e telas específicas de cada cenário.

## Cenários e dificuldades

As salas são organizadas em três níveis de dificuldade, com tempos iniciais e penalidades diferentes para cada nível.

| Dificuldade | Tempo inicial | Penalidade por erro |
|:---:|:---:|:---:|
| Fácil | 15 minutos | Sem penalidade |
| Médio | 10 minutos | 15 segundos |
| Difícil | 6 minutos | 30 segundos |

### Cenários cadastrados

| Dificuldade | Sala | Descrição |
|:---:|---|---|
| Fácil | Almoxarifado | Prateleiras vazias e uma mesa coberta de poeira. |
| Fácil | Sala de Espera | Cadeiras enfileiradas e um relógio parado. |
| Médio | Arquivo | Fichários antigos escondem mais do que papéis. |
| Médio | Apartamento | Um ambiente com vinho, um celular bloqueado e pistas para descobrir a senha. |
| Difícil | Laboratório | Luzes piscando e o cheiro de algo queimando. |
| Difícil | Sala de Observação | Um vidro espesso separa o jogador do que há lá dentro. |

O catálogo possui seis cenários cadastrados. Nesta versão, a sala **Apartamento (Médio 2)** possui interface e fluxo de jogo implementados; as demais estão cadastradas, mas ainda não possuem suas respectivas interfaces jogáveis.

## Banco de dados

O projeto utiliza **MySQL** para armazenar os dados dos jogadores e das partidas. A estrutura inicial do banco é definida no arquivo `database.sql`.

### Tabela `jogadores`

Armazena as informações das contas cadastradas:

- `id`: identificador do jogador;
- `nome`: nome informado no cadastro;
- `email`: endereço de e-mail, com restrição de unicidade;
- `senha_hash`: hash da senha;
- `criacao`: data e horário de criação da conta.

### Tabela `partidas`

Registra as partidas iniciadas pelos jogadores:

- `id`: identificador da partida;
- `jogador_id`: referência ao jogador relacionado;
- `dificuldade` e `sala`: identificação do desafio;
- `limite_segundos`: tempo inicial disponível;
- `inicio` e `fim`: horários de início e encerramento;
- `tempo_segundos`: duração registrada da partida;
- `erros`: quantidade de respostas incorretas;
- `dicas_usadas`: quantidade de dicas utilizadas;
- `status`: situação atual ou final da partida.

A coluna `jogador_id` utiliza uma chave estrangeira relacionada à tabela `jogadores`. O ranking é calculado a partir das partidas concluídas, sem necessidade de uma tabela independente para a classificação.

## Validações e segurança

O sistema possui validações tanto nos dados enviados pelos formulários quanto nas operações realizadas durante uma partida.

- **Cadastro:** verifica o preenchimento dos campos, o tamanho do nome, o formato do e-mail, o comprimento mínimo da senha e a existência de e-mail já cadastrado.
- **Autenticação:** confere as credenciais e utiliza mensagens genéricas para falhas de login.
- **Senhas:** utiliza `password_hash()` para armazenar hashes e `password_verify()` para validar as senhas informadas.
- **Banco de dados:** utiliza PDO e consultas preparadas para executar operações com os dados recebidos.
- **Sessões:** regenera o identificador da sessão após o login e controla o acesso às funcionalidades autenticadas.
- **CSRF:** utiliza tokens nas requisições POST para validar a origem das ações enviadas pelos formulários.
- **Saída de dados:** aplica escape de caracteres com `htmlspecialchars()` antes de exibir conteúdo dinâmico nas páginas.
- **Respostas dos enigmas:** normaliza as respostas antes de compará-las com a solução esperada.

## Funcionamento das partidas

Cada partida é associada a um jogador, a uma sala e a um nível de dificuldade. O tempo restante considera o limite definido para a dificuldade e a quantidade de erros cometidos.

Na sala Apartamento, o jogador precisa observar os elementos do cenário e seguir uma sequência de etapas: identificar a pista relacionada ao vinho, descobrir a palavra que desbloqueia o celular e utilizar o código revelado para abrir a porta.

O cronômetro exibido no navegador é atualizado por JavaScript, mas a verificação do tempo e o encerramento da partida são realizados no servidor. Quando o tempo termina, a partida recebe o status correspondente e a tela de jumpscare pode ser exibida conforme a preferência do jogador.

## Ranking

A classificação é calculada por sala e considera apenas partidas concluídas. Os melhores resultados são ordenados pelo menor tempo registrado. Em caso de empate, fica à frente o jogador com a menor quantidade de erros.

## Tecnologias utilizadas

| Tecnologia | Aplicação no projeto |
|---|---|
| **PHP 8.1+** | Lógica da aplicação, controladores, modelos e regras das partidas. |
| **MySQL** | Armazenamento de jogadores e partidas. |
| **HTML5** | Estrutura das páginas e dos formulários. |
| **CSS3** | Estilização das interfaces e dos cenários. |
| **JavaScript** | Interações no navegador e atualização visual do cronômetro. |
| **Composer** | Autoload das classes com o padrão PSR-4. |

## Autores

Desenvolvido por:

- **Maria Eduarda** — [GitHub](https://github.com/taleddie)
- **Vitória Karina** — [GitHub](https://github.com/vitoriab010)
- **Julia Silva**  — [GitHub](https://github.com/joleasouz)
