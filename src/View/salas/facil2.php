<?php
use App\Core\View;

$base = 'index.php?acao=';
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

<div class="cena">
    <img src="assets/img/facil2/cadeiras_relogio.jpg" alt="Cadeiras enfileiradas e um relógio parado">
</div>

<p class="legenda">
    A paciência é a chave para quem espera a hora certa.
</p>

<form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
    <?= View::csrf() ?>
    <input type="hidden" name="alvo" value="porta">
    <label>Código (3 dígitos)
        <input type="text" name="resposta" maxlength="3" inputmode="numeric" pattern="[0-9]{3}" required autofocus>
    </label>
    <button type="submit">Enviar código</button>
</form>

<script src="assets/js/cronometro.js"></script>