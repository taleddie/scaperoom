<?php
use App\Core\View;

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

<!-- Cenário Principal -->
<?php if ($vista === 'cenario'): ?>
    <div class="cena">
        <img src="assets/img/dificil1/cenario.jpg" alt="Piscina abandonada com azulejos rachados e um corrimão de metal">

        <!-- Examinar corrimão -->
        <a class="ponto" style="left:66%;top:62%;width:18%;height:10%"
            href="<?= $base ?>examinar&amp;objeto=corrimao" title="Corrimão de metal"></a>

        <!-- Examinar porta de saída -->
        <a class="ponto" style="left:13%;top:43%;width:22%;height:22%"
            href="<?= $base ?>examinar&amp;objeto=porta" title="Porta de saída"></a>
    </div>

<!-- Vista do Corrimão -->
<?php elseif ($vista === 'corrimao'): ?>
    <div class="cena">
        <img src="assets/img/dificil1/corrimao.jpg" alt="Corrimão de metal refletido na água">
    </div>

    <?php if (!$codigoResolvido): ?>
        <p class="legenda">
            A profundidade revela-se através do produto das estruturas preponderantes
            que se repetem no reflexo. O eco repete o mesmo segredo três vezes.
        </p>

        <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
            <?= View::csrf() ?>
            <input type="hidden" name="alvo" value="corrimao">
            <label>Código do corrimão (5 dígitos)
                <input type="text" name="resposta" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" required autofocus>
            </label>
            <button type="submit">Inserir código</button>
        </form>
    <?php else: ?>
        <div class="achado">
            <p>Palavra gravada no metal:</p>
            <strong class="codigo"><?= View::e($palavraVisivel) ?></strong>
        </div>
    <?php endif; ?>

    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>

<!-- Vista da Porta / Teclado de Letras -->
<?php elseif ($vista === 'porta'): ?>
    <div class="cena">
        <img src="assets/img/dificil1/porta.svg" alt="Porta de saída com um teclado de letras">
    </div>

    <p class="legenda">
        A única saída tem um teclado de letras. Algo aqui insiste em repetir o que você faz.
    </p>

    <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
        <?= View::csrf() ?>
        <input type="hidden" name="alvo" value="porta">
        <label>Palavra-chave
            <input type="text" name="resposta" maxlength="30" required autofocus>
        </label>
        <button type="submit">Abrir</button>
    </form>

    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>
<?php endif; ?>

<script src="assets/js/cronometro.js"></script>