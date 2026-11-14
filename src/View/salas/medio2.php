<?php
use App\Core\View;

/* sala medio 2 (apartamento).
   variaveis q chegam do controller: $sala, $restante, $penalidade, $erros, $mensagem,
   $vista (cenario|vinho|celular|porta), $celularAberto, $codigoVisivel
   fluxo: mesa (vinho) -> balcao (celular, senha = anagrama) -> codigo -> porta */
$base = 'index.php?acao=';
?>
<div class="topo-sala">
    <h2><?= View::e($sala['nome']) ?></h2>
    <div class="painel">
        <span>Erros: <?= (int) $erros ?> (-<?= (int) $penalidade ?>s cada)</span>
        <div id="cronometro" class="cronometro" data-restante="<?= (int) $restante ?>">--:--</div>
    </div>
</div>

<?php if (!empty($mensagem)): ?>
    <p class="mensagem"><?= View::e($mensagem) ?></p>
<?php endif; ?>

<?php if ($vista === 'cenario'): ?>
    <?php /* imagem 1: a sala toda. as areas clicaveis usam % da imagem, entao
             acompanham o tamanho dela (muda o left/top/width/height p ajustar) */ ?>
    <div class="cena">
        <img src="assets/img/m2/cenario.jpg" alt="Apartamento escuro com luzes verdes">
        <a class="ponto" style="left:47%;top:60%;width:10%;height:15%"
           href="<?= $base ?>examinar&amp;objeto=mesa" title="Mesa de centro"></a>
        <a class="ponto" style="left:66%;top:43%;width:21%;height:29%"
           href="<?= $base ?>examinar&amp;objeto=balcao" title="Balcão ao lado do fogão"></a>
    </div>
    <p class="legenda">Clique nos objetos para examinar.</p>
    <p><a class="botao" href="<?= $base ?>examinar&amp;objeto=porta">Ir até a porta</a></p>

<?php elseif ($vista === 'vinho'): ?>
    <?php /* imagem 2: garrafa com o nome em destaque */ ?>
    <!-- psiu: o nome do vinho está com as letras fora de ordem. tente um anagrama -->
    <div class="cena">
        <img src="assets/img/m2/vinho.jpeg" alt="Garrafa de vinho sobre a mesa">
    </div>
    <p class="legenda">Uma garrafa de vinho. O nome do rótulo está em destaque.</p>
    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>

<?php elseif ($vista === 'celular'): ?>
    <?php /* imagem 3: balcao com o celular */ ?>
    <div class="cena">
        <img src="assets/img/m2/celular.png" alt="Celular bloqueado no balcão">
    </div>

    <?php if (!$celularAberto): ?>
        <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
            <?= View::csrf() ?>
            <input type="hidden" name="alvo" value="celular">
            <label>O celular pede uma senha
                <input type="text" name="resposta" maxlength="30" required autofocus>
            </label>
            <button type="submit">Desbloquear</button>
        </form>
    <?php else: ?>
        <?php /* o codigo so existe no html depois de acertar a senha (o servidor confere) */ ?>
        <div class="achado">
            <p>Mensagem na tela do celular:</p>
            <strong class="codigo"><?= View::e($codigoVisivel) ?></strong>
        </div>
    <?php endif; ?>

    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>

<?php elseif ($vista === 'porta'): ?>
    <?php /* imagem 4: a porta, onde se digita o codigo de 5 digitos */ ?>
    <div class="cena">
        <img src="assets/img/m2/porta.jpeg" alt="Porta trancada">
    </div>

    <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
        <?= View::csrf() ?>
        <input type="hidden" name="alvo" value="porta">
        <label>Código da porta (5 dígitos)
            <input type="text" name="resposta" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" required autofocus>
        </label>
        <button type="submit">Abrir</button>
    </form>

    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>
<?php endif; ?>

<script src="assets/js/cronometro.js"></script>
