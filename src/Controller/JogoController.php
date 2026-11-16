<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Roteador;
use App\Core\Sessao;
use App\Core\View;
use App\DAO\PartidaDAO;
use App\Exception\CodigoIncorretoException;
use App\Exception\TempoEsgotadoException;
use App\Model\Dificuldade;
use App\Model\EnigmaCodigo;
use App\Model\Partida;
use App\Model\Sala;

// controla o jogo: escolher sala, jogar, ganhar ou perder
class JogoController
{
    // so abre a conexao com o banco qd precisa
    private ?PartidaDAO $partidas = null;

    private function partidas(): PartidaDAO
    {
        return $this->partidas ??= new PartidaDAO();
    }

    // ---------------------------------------------------------------
    // tela de escolha de sala
    // ---------------------------------------------------------------
    public function salas(): void
    {
        // voltar pra ca = sair da partida = perdeu (nao salva progresso)
        $this->partidas()->abandonarAtivas(Sessao::jogadorId());
        Sessao::limparJogo();

        $grupos = [];
        foreach (Sala::ids() as $id) {
            $dados = Sala::dados($id);
            // so deixa jogar sala q ja tem view pronta
            $dados['pronta'] = is_file(__DIR__ . '/../View/salas/' . $id . '.php');
            $grupos[$dados['dificuldade']][] = $dados;
        }

        View::renderizar('salas', [
            'titulo'          => 'Escolha a sala',
            'nome'            => Sessao::jogadorNome(),
            'grupos'          => $grupos,
            'concluidas'      => $this->partidas()->salasConcluidas(Sessao::jogadorId()),
            'jumpscareLigado' => $this->jumpscareLigado(),
        ]);
    }

    // ---------------------------------------------------------------
    // comeca a partida na sala escolhida (POST)
    // ---------------------------------------------------------------
    public function iniciar(): void
    {
        $id = (string) filter_input(INPUT_POST, 'sala');

        // sala q nao existe ou q ainda nao tem view volta pra escolha
        if (!Sala::existe($id) || !is_file(__DIR__ . '/../View/salas/' . $id . '.php')) {
            Roteador::redirecionar('index.php?acao=salas');
        }

        // so uma partida por vez
        $this->partidas()->abandonarAtivas(Sessao::jogadorId());

        $config = Dificuldade::getConfig(Sala::dificuldade($id));

        $partidaId = $this->partidas()->criar(
            Sessao::jogadorId(),
            $id,
            Sala::dificuldade($id),
            $config['limite_segundos']
        );

        // enigma fixo da sala (nao tem sorteio)
        Sessao::iniciarJogo($partidaId, $id, [
            'codigo'  => Sala::codigo($id),
            'palavra' => Sala::palavra($id),
            'cartao'  => Sala::cartao($id),
        ]);

        Roteador::redirecionar($this->urlSala($id));
    }

    // ---------------------------------------------------------------
    // mostra a sala (uma view por sala). ?vista= diz qual imagem aparece
    // ---------------------------------------------------------------
    public function sala(): void
    {
        $this->proteger(function () {
            [$partida, $id, $jogo] = $this->carregar();

            $etapas = $jogo['etapas'];
            $config = $this->configSala($id);

            // vista pedida na url, so aceita se a etapa anterior ja foi feita
            $vista = (string) filter_input(INPUT_GET, 'vista');
            $liberada = isset($config['vistas'][$vista])
                && !empty($etapas[$config['vistas'][$vista]]);
            if (!$liberada) {
                $vista = 'cenario';
            }
            // pista na url: na vista indicada, a url precisa mostrar o codigo em ASCII.
            // o jogador so chega aqui depois de ligar a luz (a vista ja foi liberada acima)
            if (isset($config['pista_url']) && $vista === $config['pista_url']['vista']) {
                $param = $config['pista_url']['param'];
                $pista = implode('', array_map('ord', str_split($jogo['codigo'])));

                if ((string) filter_input(INPUT_GET, $param) !== $pista) {
                    Roteador::redirecionar($this->urlSala($id, $vista) . '&' . $param . '=' . $pista);
                }
            }

            // a view so recebe o q o jogador JA desbloqueou.
            // o segredo da proxima etapa nao vai no html antes da hora
            View::renderizar('salas/' . $id, [
                'titulo'           => Sala::nome($id),
                'sala'             => Sala::dados($id),
                'restante'         => $partida->tempoRestante(),
                'penalidade'       => $partida->penalidadeErro(),
                'erros'            => $partida->getErros(),
                'mensagem'         => Sessao::pegarMensagem(),
                'vista'            => $vista,
                'etapas'           => $etapas,
                'celularAberto'    => !empty($etapas['celular_aberto']),
                'codigoVisivel'    => !empty($etapas['celular_aberto']) ? $jogo['codigo'] : null,
                'codigoResolvido'  => !empty($etapas['codigo_resolvido']),
                'palavraVisivel'   => !empty($etapas['codigo_resolvido']) ? $jogo['palavra'] : null,
            ]);
        });
    }

    // ---------------------------------------------------------------
    // clique num objeto da sala (mesa, balcao, porta)
    // ---------------------------------------------------------------
    public function examinar(): void
    {
        $this->proteger(function () {
            [, $id] = $this->carregar();

            $objeto = (string) filter_input(INPUT_GET, 'objeto');
            $config = $this->configSala($id);

            if (!isset($config['objetos'][$objeto])) {
                Roteador::redirecionar($this->urlSala($id));
            }

            $alvo = $config['objetos'][$objeto];
            Sessao::marcarEtapa($alvo['etapa']);

            Roteador::redirecionar($this->urlSala($id, $alvo['vista']));
        });
    }

    // ---------------------------------------------------------------
    // tentativa de senha do celular ou codigo da porta (POST)
    // ---------------------------------------------------------------
    public function tentar(): void
    {
        $this->proteger(function () {
            [$partida, $id, $jogo] = $this->carregar();

            $alvo     = (string) filter_input(INPUT_POST, 'alvo');
            $resposta = trim((string) filter_input(INPUT_POST, 'resposta'));
            $resposta = function_exists('mb_substr')
                ? mb_substr($resposta, 0, 30)
                : substr($resposta, 0, 30);
            $etapas   = $jogo['etapas'];
            $config   = $this->configSala($id);

            if (!isset($config['alvos'][$alvo])) {
                Roteador::redirecionar($this->urlSala($id));
            }

            $regra = $config['alvos'][$alvo];

            // o servidor nao confia no navegador: confere se o objeto ja foi examinado
            // (salas simples, sem objeto, nao tem etapa para conferir)
            if (!empty($regra['etapa']) && empty($etapas[$regra['etapa']])) {
                Roteador::redirecionar($this->urlSala($id));
            }

            // etapa ja concluida: nao deixa repetir a tentativa
            if (!empty($regra['concluida']) && !empty($etapas[$regra['concluida']])) {
                Roteador::redirecionar($this->urlSala($id));
            }

            $volta = $this->urlSala($id, $regra['vista']);

            // sem o requisito (ex.: codigo do celular), nao conta como erro
            if (!empty($regra['requisito']) && empty($etapas[$regra['requisito']])) {
                Sessao::mensagem($regra['mensagem_travada']);
                Roteador::redirecionar($volta);
            }

            $solucao = $jogo[$regra['solucao']];

            try {
                (new EnigmaCodigo($solucao))->tentar($resposta);
            } catch (CodigoIncorretoException $e) {
                // errou: soma erro no banco. o tempo perdido sai no proximo carregar()
                $this->partidas()->registrarErro($partida->getId());

                $pen   = $partida->penalidadeErro();
                $texto = $e->getMessage() . ($pen > 0 ? " Penalidade: -{$pen}s." : '');

                Sessao::mensagem($texto);
                Roteador::redirecionar($volta);
            }

            // primeira etapa: marca o avanco e revela a pista da proxima
            if (!empty($regra['concluida'])) {
                Sessao::marcarEtapa($regra['concluida']);
                Sessao::mensagem($regra['mensagem']);
                Roteador::redirecionar($volta);
            }

            // acertou a porta: escapou
            $this->partidas()->finalizar($partida->getId(), 'concluida');
            Roteador::redirecionar('index.php?acao=vitoria');
        });
    }

    // o relogio do navegador chegou a zero: quem decide e o servidor
    public function verificarTempo(): void
    {
        $this->proteger(function () {
            [, $id] = $this->carregar();
            // se ainda sobrou tempo no servidor, so volta pra sala
            Roteador::redirecionar($this->urlSala($id));
        });
    }

    // ---------------------------------------------------------------
    // fim de jogo
    // ---------------------------------------------------------------
    public function vitoria(): void
    {
        $linha = $this->partidaDaSessao();

        if ($linha === null || $linha['status'] !== 'concluida') {
            Roteador::redirecionar('index.php?acao=salas');
        }

        View::renderizar('vitoria', [
            'titulo' => 'Você saiu!',
            'sala'   => Sala::dados($linha['sala']),
            'tempo'  => (int) $linha['tempo_segundos'],
            'erros'  => (int) $linha['erros'],
        ]);
    }

    public function jumpscare(): void
    {
        $linha = $this->partidaDaSessao();

        if ($linha === null) {
            Roteador::redirecionar('index.php?acao=salas');
        }

        // se o tempo acabou mas a partida ainda nao foi fechada, fecha agora
        if ($linha['status'] === 'em_andamento' && Partida::fromArray($linha)->tempoEsgotado()) {
            $this->partidas()->finalizar((int) $linha['id'], 'tempo_esgotado');
            $linha['status'] = 'tempo_esgotado';
        }

        // so leva o susto quem perdeu de verdade. digitar a url a toa nao funciona
        if ($linha['status'] !== 'tempo_esgotado') {
            Roteador::redirecionar('index.php?acao=salas');
        }

        View::renderizar('jumpscare', ['ligado' => $this->jumpscareLigado()], false);
    }

    // cookie (aula 31): liga/desliga o jumpscare
    public function preferencias(): void
    {
        $valor = filter_input(INPUT_POST, 'jumpscare') === 'off' ? 'off' : 'on';

        setcookie('jumpscare', $valor, [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        Roteador::redirecionar('index.php?acao=salas');
    }

    // ===============================================================
    // auxiliares
    // ===============================================================

    private function jumpscareLigado(): bool
    {
        return ($_COOKIE['jumpscare'] ?? 'on') !== 'off';
    }

    // roda a acao. se o tempo acabar, fecha a partida e manda pro jumpscare
    private function proteger(callable $acao): void
    {
        try {
            $acao();
        } catch (TempoEsgotadoException $e) {
            $this->partidas()->finalizar($e->getPartidaId(), 'tempo_esgotado');
            Roteador::redirecionar('index.php?acao=jumpscare');
        }
    }

    // confere partida, dono e tempo. devolve [Partida, id da sala, estado do jogo]
    private function carregar(): array
    {
        $linha = $this->partidaDaSessao();

        if ($linha === null) {
            Roteador::redirecionar('index.php?acao=salas');
        }
        if ($linha['status'] === 'concluida') {
            Roteador::redirecionar('index.php?acao=vitoria');
        }
        if ($linha['status'] === 'tempo_esgotado') {
            Roteador::redirecionar('index.php?acao=jumpscare');
        }
        if ($linha['status'] !== 'em_andamento') {
            Roteador::redirecionar('index.php?acao=salas');
        }

        $partida = Partida::fromArray($linha);

        // cronometro no servidor: toda requisicao passa por aqui (ja desconta os erros)
        if ($partida->tempoEsgotado()) {
            throw new TempoEsgotadoException($partida->getId());
        }

        return [$partida, $partida->getSala(), Sessao::jogo()];
    }

    // linha da partida da sessao, so se for do jogador logado
    private function partidaDaSessao(): ?array
    {
        $jogo = Sessao::jogo();
        if ($jogo === null) {
            return null;
        }

        $linha = $this->partidas()->buscarPorId($jogo['partida_id']);

        if ($linha === null || (int) $linha['jogador_id'] !== Sessao::jogadorId()) {
            return null;
        }
        return $linha;
    }

    // cada sala tem seus objetos, vistas e etapas. o resto do fluxo e igual
    private function configSala(string $id): array
    {
        return match ($id) {
            'medio2' => [
                'vistas' => [
                    'vinho'   => 'mesa',
                    'celular' => 'balcao',
                    'porta'   => 'porta',
                ],
                'objetos' => [
                    'mesa'   => ['etapa' => 'mesa',   'vista' => 'vinho'],
                    'balcao' => ['etapa' => 'balcao', 'vista' => 'celular'],
                    'porta'  => ['etapa' => 'porta',  'vista' => 'porta'],
                ],
                'alvos' => [
                    'celular' => [
                        'etapa'            => 'balcao',
                        'concluida'        => 'celular_aberto',
                        'solucao'          => 'palavra',
                        'vista'            => 'celular',
                        'mensagem'         => 'O celular desbloqueou e mostra uma mensagem.',
                    ],
                    'porta' => [
                        'etapa'            => 'porta',
                        'requisito'        => 'celular_aberto',
                        'solucao'          => 'codigo',
                        'vista'            => 'porta',
                        'mensagem_travada' => 'A porta não abre. Ainda falta descobrir o código.',
                    ],
                ],
            ],
            'facil1' => [
                'vistas' => [
                    'uv'     => 'interruptor',
                    'mesa'   => 'mesa',
                    'carta'  => 'documentos',
                    'tabela' => 'documentos',
                    'porta'  => 'porta',
                ],
                'objetos' => [
                    'interruptor' => ['etapa' => 'interruptor', 'vista' => 'uv'],
                    'mesa'        => ['etapa' => 'mesa',        'vista' => 'mesa'],
                    'documentos'  => ['etapa' => 'documentos',  'vista' => 'carta'],
                    'porta'       => ['etapa' => 'porta',       'vista' => 'porta'],
                ],
                'alvos' => [
                    'porta' => [
                        'etapa'   => 'porta',
                        'solucao' => 'codigo',
                        'vista'   => 'porta',
                    ],
                ],
                // na vista 'uv' a url mostra o codigo em ASCII (?ref=69887384)
                'pista_url' => ['vista' => 'uv', 'param' => 'ref'],
            ],
            'dificil2' => [
                'vistas' => [
                    'telefone'   => 'telefone',
                    'gaveta'     => 'gaveta',
                    'camera'     => 'camera',
                    'cam5'       => 'cam5',
                    'computador' => 'computador',
                ],
                'objetos' => [
                    'telefone'   => ['etapa' => 'telefone',   'vista' => 'telefone'],
                    'gaveta'     => ['etapa' => 'gaveta',     'vista' => 'gaveta'],
                    'camera'     => ['etapa' => 'camera',     'vista' => 'camera'],
                    'cam5'       => ['etapa' => 'cam5',       'vista' => 'cam5'],
                    'computador' => ['etapa' => 'computador', 'vista' => 'computador'],
                ],
                'alvos' => [
                    'gaveta' => [
                        'etapa'     => 'gaveta',
                        'concluida' => 'gaveta_aberta',
                        'solucao'   => 'codigo',
                        'vista'     => 'gaveta',
                        'mensagem'  => 'A gaveta abriu.',
                    ],
                    'cam5' => [
                        'etapa'     => 'cam5',
                        'concluida' => 'cam5_liberada',
                        'solucao'   => 'palavra',
                        'vista'     => 'cam5',
                        'mensagem'  => 'Câmera liberada.',
                    ],
                    'computador' => [
                        'etapa'   => 'computador',
                        'solucao' => 'cartao',
                        'vista'   => 'computador',
                    ],
                ],
            ],
            'facil2' => [
                'vistas'  => [],
                'objetos' => [],
                'alvos'   => [
                    'porta' => [
                        'etapa'   => null,
                        'solucao' => 'codigo',
                        'vista'   => 'cenario',
                    ],
                ],
            ],
            'dificil1' => [
                'vistas' => [
                    'corrimao' => 'corrimao',
                    'porta'    => 'porta',
                ],
                'objetos' => [
                    'corrimao' => ['etapa' => 'corrimao', 'vista' => 'corrimao'],
                    'porta'    => ['etapa' => 'porta',    'vista' => 'porta'],
                ],
                'alvos' => [
                    'corrimao' => [
                        'etapa'            => 'corrimao',
                        'concluida'        => 'codigo_resolvido',
                        'solucao'          => 'codigo',
                        'vista'            => 'corrimao',
                        'mensagem'         => 'O mecanismo cede e revela uma palavra gravada no metal.',
                    ],
                    'porta' => [
                        'etapa'            => 'porta',
                        'requisito'        => 'codigo_resolvido',
                        'solucao'          => 'palavra',
                        'vista'            => 'porta',
                        'mensagem_travada' => 'A porta não abre. O mecanismo da piscina ainda está travado.',
                    ],
                ],
            ],
            default => throw new \InvalidArgumentException('Sala sem configuração de jogo.'),
        };
    }

    // url da sala (vista = qual imagem mostrar)
    private function urlSala(string $id, ?string $vista = null): string
    {
        $url = 'index.php?acao=sala&sala=' . $id;
        if ($vista !== null) {
            $url .= '&vista=' . $vista;
        }
        return $url;
    }
}
