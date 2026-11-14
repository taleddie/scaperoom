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

            // vista pedida na url, so aceita se a etapa anterior ja foi feita
            $vista = (string) filter_input(INPUT_GET, 'vista');
            $liberada = match ($vista) {
                'vinho'   => $etapas['mesa'],
                'celular' => $etapas['balcao'],
                'porta'   => $etapas['porta'],
                default   => false,
            };
            if (!$liberada) {
                $vista = 'cenario';
            }

            // a view so recebe o q o jogador JA desbloqueou.
            // o codigo da porta nao vai no html antes de acertar a senha do celular
            View::renderizar('salas/' . $id, [
                'titulo'        => Sala::nome($id),
                'sala'          => Sala::dados($id),
                'restante'      => $partida->tempoRestante(),
                'penalidade'    => $partida->penalidadeErro(),
                'erros'         => $partida->getErros(),
                'mensagem'      => Sessao::pegarMensagem(),
                'vista'         => $vista,
                'celularAberto' => $etapas['celular_aberto'],
                'codigoVisivel' => $etapas['celular_aberto'] ? $jogo['codigo'] : null,
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

            // objeto => [etapa q marca, vista q abre]
            $objetos = [
                'mesa'   => ['mesa',   'vinho'],
                'balcao' => ['balcao', 'celular'],
                'porta'  => ['porta',  'porta'],
            ];

            if (!isset($objetos[$objeto])) {
                Roteador::redirecionar($this->urlSala($id));
            }

            [$etapa, $vista] = $objetos[$objeto];
            Sessao::marcarEtapa($etapa);

            Roteador::redirecionar($this->urlSala($id, $vista));
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
            $resposta = mb_substr(trim((string) filter_input(INPUT_POST, 'resposta')), 0, 30);
            $etapas   = $jogo['etapas'];

            // descobre a resposta certa conferindo se a etapa anterior foi feita
            // (o servidor nao confia no navegador)
            if ($alvo === 'celular' && $etapas['balcao'] && !$etapas['celular_aberto']) {
                $solucao = $jogo['palavra'];
                $volta   = $this->urlSala($id, 'celular');
            } elseif ($alvo === 'porta' && $etapas['porta']) {
                if (!$etapas['celular_aberto']) {
                    // sem codigo ainda: nao conta como erro
                    Sessao::mensagem('A porta não abre. Ainda falta descobrir o código.');
                    Roteador::redirecionar($this->urlSala($id, 'porta'));
                }
                $solucao = $jogo['codigo'];
                $volta   = $this->urlSala($id, 'porta');
            } else {
                Roteador::redirecionar($this->urlSala($id));
            }

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

            if ($alvo === 'celular') {
                Sessao::marcarEtapa('celular_aberto');
                Sessao::mensagem('O celular desbloqueou e mostra uma mensagem.');
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
