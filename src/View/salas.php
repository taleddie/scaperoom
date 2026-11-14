<?php
use App\Core\View;
use App\Model\Dificuldade;

$rotulos = ['facil' => 'Fácil', 'medio' => 'Médio', 'dificil' => 'Difícil'];
?>
<h2>Olá, <?= View::e($nome) ?>. Escolha uma sala</h2>

<p class="aviso">Este jogo tem sustos e sons altos. Se o tempo acabar, algo vai te achar.</p>
<p>Sair da partida no meio = perder. Não tem como salvar.</p>

<?php foreach ($grupos as $dif => $lista): ?>
    <?php $config = Dificuldade::getConfig($dif); ?>
    <section class="grupo">
        <h3>
            <?= View::e($rotulos[$dif]) ?>
            <small>
                <?= (int) ($config['limite_segundos'] / 60) ?> min
                <?php if ($config['penalidade_erro'] > 0): ?>
                    · erro = -<?= (int) $config['penalidade_erro'] ?>s
                <?php endif; ?>
            </small>
        </h3>

        <div class="cartoes">
            <?php foreach ($lista as $sala): ?>
                <?php $feita = in_array($sala['id'], $concluidas, true); ?>
                <form method="post" action="index.php?acao=iniciar" class="cartao">
                    <?= View::csrf() ?>
                    <input type="hidden" name="sala" value="<?= View::e($sala['id']) ?>">
                    <strong><?= View::e($sala['nome']) ?></strong>
                    <span class="desc"><?= View::e($sala['descricao']) ?></span>
                    <?php if ($feita): ?>
                        <span class="selo">Concluída</span>
                    <?php endif; ?>
                    <?php if ($sala['pronta']): ?>
                        <button type="submit"><?= $feita ? 'Jogar de novo' : 'Jogar' ?></button>
                    <?php else: ?>
                        <button type="button" disabled>Em breve</button>
                    <?php endif; ?>
                </form>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<hr>

<form method="post" action="index.php?acao=preferencias" class="linha">
    <?= View::csrf() ?>
    <span>Jumpscare: <strong><?= $jumpscareLigado ? 'ligado' : 'desligado' ?></strong></span>
    <input type="hidden" name="jumpscare" value="<?= $jumpscareLigado ? 'off' : 'on' ?>">
    <button type="submit"><?= $jumpscareLigado ? 'Desligar' : 'Ligar' ?></button>
</form>

<p><a href="index.php?acao=ranking">Ver ranking</a></p>

<form method="post" action="index.php?acao=sair">
    <?= View::csrf() ?>
    <button type="submit">Sair da conta</button>
</form>
