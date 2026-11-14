<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tempo esgotado</title>
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body class="jumpscare-pagina">

<?php if ($ligado): ?>
    <div id="susto" class="susto"><span>:</span></div>
<?php endif; ?>

<div id="gameover" class="gameover" <?= $ligado ? 'hidden' : '' ?>>
    <h1>Tempo esgotado</h1>
    <p>Você não conseguiu sair.</p>
    <a class="botao" href="index.php?acao=salas">Tentar de novo</a>
</div>

<?php if ($ligado): ?>
<script>
// susto: o barulho e gerado pelo proprio navegador (sem arquivo de audio).
// so toca pq o jogador ja clicou no site antes
(function () {
    try {
        var ctx = new (window.AudioContext || window.webkitAudioContext)();
        var ganho = ctx.createGain();
        ganho.gain.value = 0.4;
        ganho.connect(ctx.destination);
        [110, 117, 233].forEach(function (freq) {
            var osc = ctx.createOscillator();
            osc.type = 'sawtooth';
            osc.frequency.value = freq;
            osc.connect(ganho);
            osc.start();
            osc.stop(ctx.currentTime + 1.5);
        });
    } catch (e) { /* sem som, o susto visual continua */ }

    // depois de 3s mostra o game over
    setTimeout(function () {
        document.getElementById('susto').hidden = true;
        document.getElementById('gameover').hidden = false;
    }, 3000);
})();
</script>
<?php endif; ?>

</body>
</html>
