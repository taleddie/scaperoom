<?php use App\Core\View; ?>
<h2>Cadastro</h2>

<?php if (!empty($erro)): ?>
    <p class="erro"><?= View::e($erro) ?></p>
<?php endif; ?>

<form method="post" action="index.php?acao=cadastrar" class="caixa">
    <?= View::csrf() ?>
    <label>Nome <input type="text" name="nome" maxlength="80" required value="<?= View::e($nome ?? '') ?>"></label>
    <label>E-mail <input type="email" name="email" required value="<?= View::e($email ?? '') ?>"></label>
    <label>Senha (mín. 6) <input type="password" name="senha" required></label>
    <button type="submit">Criar conta</button>
</form>

<p>Já tem conta? <a href="index.php?acao=login">Entrar</a></p>
