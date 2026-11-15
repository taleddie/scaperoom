<!--

o dono deste telefone adora roedores
tente um anagrama

-->
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

<!-- cenario principal -->
<?php if ($vista === 'cenario'): ?>
    <div class="cena">
        <img src="assets/img/m2/cenario.jpg" alt="Apartamento escuro com luzes verdes">
        
        <!-- examinar mesa -->
        <a class="ponto" style="left:47%;top:60%;width:10%;height:15%"
            href="<?= $base ?>examinar&amp;objeto=mesa" title="Mesa de centro"></a>
            
        <!-- examinar balcao -->
        <a class="ponto" style="left:63%;top:43%;width:12%;height:10%"
            href="<?= $base ?>examinar&amp;objeto=balcao" title="Balcão ao lado do fogão"></a>

        <!-- examinar porta -->
        <a class="ponto" style="left:23%;top:27%;width:12%;height:30%"
            href="<?= $base ?>examinar&amp;objeto=porta" title="Porta trancada"></a>
    </div>

    <!-- vista da mesa -->
<?php elseif ($vista === 'vinho'): ?>
    <div class="cena">
        <img src="assets/img/m2/vinho.png" alt="Garrafa de vinho sobre a mesa">
    </div>
    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>

    <!-- balcao com celular -->
<?php elseif ($vista === 'celular'): ?>
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
        <div class="achado">
            <p>Mensagem na tela do celular:</p>
            <strong class="codigo"><?= View::e($codigoVisivel) ?></strong>
        </div>
    <?php endif; ?>

    <p><a class="botao" href="<?= $base ?>sala&amp;sala=<?= View::e($sala['id']) ?>">Voltar</a></p>

    <!-- porta -->
<?php elseif ($vista === 'porta'): ?>
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
