<?php use App\Core\View; use App\Model\Sala; ?>
<h2>Ranking</h2>

<nav class="abas">
    <?php foreach ($salas as $s): ?>
        <a href="index.php?acao=ranking&amp;sala=<?= View::e($s) ?>" class="<?= $s === $atual ? 'ativa' : '' ?>">
            <?= View::e(Sala::nome($s)) ?>
        </a>
    <?php endforeach; ?>
</nav>

<h3><?= View::e(Sala::nome($atual)) ?></h3>

<?php if (empty($ranking)): ?>
    <p>Ninguém saiu desta sala ainda.</p>
<?php else: ?>
    <table>
        <tr><th>#</th><th>Jogador</th><th>Tempo</th><th>Erros</th></tr>
        <?php foreach ($ranking as $i => $linha): ?>
            <?php $t = (int) $linha['tempo_segundos']; ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= View::e($linha['nome']) ?></td>
                <td><?= View::e(sprintf('%02d:%02d', intdiv($t, 60), $t % 60)) ?></td>
                <td><?= (int) $linha['erros'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<p><a href="index.php?acao=salas">Voltar às salas</a></p>
