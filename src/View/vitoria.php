<?php use App\Core\View; ?>
<h2>Você saiu!</h2>

<p>Sala: <strong><?= View::e($sala['nome']) ?></strong></p>
<p>Tempo: <strong><?= View::e(sprintf('%02d:%02d', intdiv($tempo, 60), $tempo % 60)) ?></strong></p>
<p>Erros: <strong><?= (int) $erros ?></strong></p>

<p>
    <a href="index.php?acao=ranking&amp;sala=<?= View::e($sala['id']) ?>">Ranking desta sala</a> ·
    <a href="index.php?acao=salas">Escolher outra sala</a>
</p>
