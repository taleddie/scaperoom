<?php
use App\Core\View;
use App\Model\Dificuldade;

$rotulos = ['facil' => 'Fácil', 'medio' => 'Médio', 'dificil' => 'Difícil'];
?>
<div class="barra-op">
    <span>operador: <strong><?= View::e($nome) ?></strong></span>
    <span class="barra-links">
        <a href="index.php?acao=ranking">ranking</a>
        <form method="post" action="index.php?acao=sair">
            <?= View::csrf() ?>
            <button type="submit" class="link">sair</button>
        </form>
    </span>
</div>

<h2 class="titulo-monitores">Selecione uma câmera</h2>
<p class="regras">
    Sair da partida no meio = perder, não tem como salvar. Se o tempo acabar, algo vai te achar.
</p>

<div class="colunas">
<?php foreach ($grupos as $dif => $lista): ?>
    <?php $config = Dificuldade::getConfig($dif); ?>
    <section class="coluna">
        <h3>
            <?= View::e($rotulos[$dif]) ?>
            <small>
                <?= (int) ($config['limite_segundos'] / 60) ?> min
                <?php if ($config['penalidade_erro'] > 0): ?>
                    · erro -<?= (int) $config['penalidade_erro'] ?>s
                <?php endif; ?>
            </small>
        </h3>

        <?php foreach ($lista as $sala): ?>
            <?php $feita = in_array($sala['id'], $concluidas, true); ?>
            <form method="post" action="index.php?acao=iniciar">
                <?= View::csrf() ?>
                <input type="hidden" name="sala" value="<?= View::e($sala['id']) ?>">
                <?php /* a tela inteira e o botao: clicou, comecou */ ?>
                <button type="submit" class="monitor <?= $sala['pronta'] ? '' : 'sem-sinal' ?>" <?= $sala['pronta'] ? '' : 'disabled' ?>>
                    <span class="tela" <?= $sala['capa'] ? 'style="background-image:url(' . View::e($sala['capa']) . ')"' : '' ?>>
                        <span class="cam">CAM <?= sprintf('%02d', $sala['cam']) ?></span>
                        <?php if (!$sala['pronta']): ?>
                            <span class="estado">SEM SINAL</span>
                        <?php elseif ($feita): ?>
                            <span class="estado ok">CONCLUÍDA</span>
                        <?php endif; ?>
                    </span>
                    <span class="legenda-monitor">
                        <strong><?= View::e($sala['nome']) ?></strong>
                        <em><?= $sala['pronta'] ? ($feita ? 'jogar de novo' : 'jogar') : 'em breve' ?></em>
                    </span>
                </button>
            </form>
        <?php endforeach; ?>
    </section>
<?php endforeach; ?>
</div>

<form method="post" action="index.php?acao=preferencias" class="rodape-opcoes">
    <?= View::csrf() ?>
    <input type="hidden" name="jumpscare" value="<?= $jumpscareLigado ? 'off' : 'on' ?>">
    <span>jumpscare: <?= $jumpscareLigado ? 'ligado' : 'desligado' ?></span>
    <button type="submit" class="link"><?= $jumpscareLigado ? 'desligar' : 'ligar' ?></button>
</form>
