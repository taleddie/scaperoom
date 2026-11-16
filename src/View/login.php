<?php
use App\Core\View;

// aba q abre primeiro: 'login' (padrao) ou 'cadastro' (qd vem do link ou de erro no cadastro)
$aba = $aba ?? 'login';
?>
<div class="cartao-login">
    <h1>[nome a definir]</h1>
    <h2>Identifique-se antes que algo te encontre.</h2>

    <div class="abas-login">
        <button type="button" id="btn-login" class="<?= $aba === 'login' ? 'ativa' : '' ?>" onclick="mudarAba('login')">Entrar</button>
        <button type="button" id="btn-cadastro" class="<?= $aba === 'cadastro' ? 'ativa' : '' ?>" onclick="mudarAba('cadastro')">Registrar</button>
    </div>

    <?php if (!empty($erro)): ?>
        <p class="erro"><?= View::e($erro) ?></p>
    <?php endif; ?>

    <form id="form-login" method="post" action="index.php?acao=logar" <?= $aba === 'login' ? '' : 'hidden' ?>>
        <?= View::csrf() ?>
        <label>E-mail <input type="email" name="email" required></label>
        <label>Senha <input type="password" name="senha" required></label>
        <button type="submit">Retomar jogo</button>
    </form>

    <form id="form-cadastro" method="post" action="index.php?acao=cadastrar" <?= $aba === 'cadastro' ? '' : 'hidden' ?>>
        <?= View::csrf() ?>
        <label>Nome <input type="text" name="nome" maxlength="80" required value="<?= View::e($nome ?? '') ?>"></label>
        <label>E-mail <input type="email" name="email" required value="<?= View::e($email ?? '') ?>"></label>
        <label>Senha (mín. 6) <input type="password" name="senha" required></label>
        <button type="submit">Entrar no labirinto</button>
    </form>
</div>

<script>
// troca entre as duas abas (so mostra/esconde, os dois forms ja estao na pagina)
function mudarAba(aba) {
    var entrar = aba === 'login';
    document.getElementById('form-login').hidden = !entrar;
    document.getElementById('form-cadastro').hidden = entrar;
    document.getElementById('btn-login').classList.toggle('ativa', entrar);
    document.getElementById('btn-cadastro').classList.toggle('ativa', !entrar);
}
</script>
