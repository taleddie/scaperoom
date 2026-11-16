<?php
use App\Core\View;

$base  = 'index.php?acao=';
$idUrl = $base . 'sala&amp;sala=' . View::e($sala['id']);

// "voltar" devolve o jogador pra sala: se ele ja ligou a luz UV, volta pra UV
$voltar = !empty($etapas['interruptor']) ? $idUrl . '&amp;vista=uv' : $idUrl;
?>
<div class="topo-sala">
    <h2><?= View::e($sala['nome']) ?></h2>
    <div class="painel">
        <?php if (isset($erros)): ?>
            <span>Erros: <?= (int) $erros ?> (-<?= (int) $penalidade ?>s cada)</span>
        <?php endif; ?>
        <div id="cronometro" class="cronometro" data-restante="<?= (int) $restante ?>">--:--</div>
    </div>
</div>

<?php if (!empty($mensagem)): ?>
    <p class="mensagem"><?= View::e($mensagem) ?></p>
<?php endif; ?>

<!-- cenario principal: luz normal (cenario) ou luz UV (uv). mesmos pontos clicaveis nas duas -->
<?php if ($vista === 'cenario' || $vista === 'uv'): ?>
    <?php $uv = ($vista === 'uv'); ?>
    <div class="cena">
        <img src="assets/img/facil1/<?= $uv ? 'Backrooms_com_UV' : 'Backrooms_sem_UV' ?>.png"
            alt="<?= $uv ? 'Corredores sob luz ultravioleta, com setas brilhando nas paredes' : 'Corredores vazios de papel de parede amarelo, com uma mesa ao fundo' ?>">

        <!-- interruptor: na luz normal liga a UV, na UV volta pra luz normal -->
        <a class="ponto" style="left:37.2%;top:42.6%;width:3.4%;height:9%"
            href="<?= $uv ? $idUrl : $base . 'examinar&amp;objeto=interruptor' ?>" title="Interruptor"></a>

        <!-- examinar mesa -->
        <a class="ponto" style="left:41.9%;top:46.5%;width:21.7%;height:20.5%"
            href="<?= $base ?>examinar&amp;objeto=mesa" title="Mesa"></a>

        <!-- corredor do fundo: leva pra porta -->
        <a class="ponto" style="left:72.5%;top:34%;width:8%;height:20%"
            href="<?= $base ?>examinar&amp;objeto=porta" title="Corredor ao fundo"></a>
    </div>

<!-- mesa: pergunta se quer ler os documentos -->
<?php elseif ($vista === 'mesa'): ?>
    <div class="cena">
        <img src="assets/img/facil1/Backrooms_mesa.png" alt="Mesa com um notebook apagado e pilhas de papéis">
    </div>

    <div class="caixa">
        <p>Há documentos sobre a mesa. Deseja ler?</p>
        <div class="linha">
            <a class="botao" href="<?= $base ?>examinar&amp;objeto=documentos">Sim</a>
            <a class="botao" href="<?= $voltar ?>">Não</a>
        </div>
    </div>

<!-- documento 1: carta. a seta branca leva pro documento 2 -->
<?php elseif ($vista === 'carta'): ?>
    <div class="cena">
        <img src="assets/img/facil1/DOC_carta.png" alt="Memorando de campo escrito pelo Dr. Jeremias">

        <!-- seta branca: proximo documento -->
        <a class="ponto" style="left:92.5%;top:42%;width:5.5%;height:13%"
            href="<?= $idUrl ?>&amp;vista=tabela" title="Próximo documento"></a>
    </div>
    <p><a class="botao" href="<?= $voltar ?>">Voltar</a></p>

<!-- documento 2: tabela ASCII. a seta branca volta pra carta -->
<?php elseif ($vista === 'tabela'): ?>
    <div class="cena">
        <img src="assets/img/facil1/DOC_Tabela_ascii.png" alt="Tabela ASCII com os valores em decimal, hexadecimal e o caractere">

        <!-- seta branca: volta pro documento 1 -->
        <a class="ponto" style="left:92.5%;top:42%;width:5.5%;height:13%"
            href="<?= $idUrl ?>&amp;vista=carta" title="Documento anterior"></a>
    </div>
    <p><a class="botao" href="<?= $voltar ?>">Voltar</a></p>

<!-- porta: codigo de saida -->
<?php elseif ($vista === 'porta'): ?>
    <div class="cena">
        <img src="assets/img/facil1/Backrooms_porta.png" alt="Corredor sem saída aparente, com uma porta e um teclado ao lado">
    </div>

    <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
        <?= View::csrf() ?>
        <input type="hidden" name="alvo" value="porta">
        <label>Código de saída
            <input type="text" name="resposta" maxlength="10" required autofocus>
        </label>
        <button type="submit">Abrir</button>
    </form>

    <p><a class="botao" href="<?= $voltar ?>">Voltar</a></p>
<?php endif; ?>

<script src="assets/js/cronometro.js"></script>