<?php use App\Core\View; ?>
<h2>Entrar</h2>

<?php if (!empty($erro)): ?>
    <p class="erro"><?= View::e($erro) ?></p>
<?php endif; ?>

<form method="post" action="index.php?acao=logar" class="caixa">
    <?= View::csrf() ?>
    <label>E-mail <input type="email" name="email" required></label>
    <label>Senha <input type="password" name="senha" required></label>
    <button type="submit">Entrar</button>
</form>

<p>Não tem conta? <a href="index.php?acao=cadastro">Cadastre-se</a></p>
