<!--


tente um anagrama


-->

<?php
/** @var array $partida sados da partida atual vindos do banco */
/** @var array $etapasDesbloqueadas estado atual do progresso na sessão */
$etapaMesa = $etapasDesbloqueadas['mesa'] ?? false;
$etapaCelular = $etapasDesbloqueadas['celular'] ?? false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escape Room // Nível Médio</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="sala-backrooms">

    <div class="hud-topo">
        <span>SALA: MEDIO1 | TEMPO RESTANTE: <span id="tempo-restante">--:--</span></span>
        <a href="index.php?acao=salas" class="btn-sair">[ Abandonar Sala ]</a>
    </div>

    <div class="ambiente-container">
        <!-- cenario -->
        <?php if (!$etapaMesa && !$etapaCelular): ?>
            <div class="interativo-wrapper">
                <img src="assets/img/m2/cenario.jpg" alt="Sala escura com luzes verdes" usemap="#mapa-geral">
                <map name="mapa-geral">
                    <!-- clicavel com vinho -->
                    <area coords="400,500,550,650" href="index.php?acao=examinar&alvo=mesa" alt="Mesa">
                    <!-- clicavel balcao -->
                    <area coords="700,450,850,600" href="index.php?acao=examinar&alvo=celular" alt="Balcão">
                </map>
            </div>
        <?php endif; ?>

        <!-- mesa -->
        <?php if ($etapaMesa && !$etapaCelular): ?>
            <div class="zoom-objeto">
                <img src="assets/img/m2/vinho.png" alt="Garrafa de vinho sobre a mesa">
                <a href="index.php?acao=sala&sala=medio1" class="btn-voltar">&lt; Voltar para a sala</a>
            </div>
        <?php endif; ?>

        <!-- balcão -->
        <?php if ($etapaCelular): ?>
            <div class="zoom-objeto">
                <img src="assets/img/m2/celular.jpg" alt="Celular brilhando no balcão ao lado do fogão">
                <div class="interface-celular">
                    <h3>DISPOSITIVO BLOQUEADO</h3>
                    <p>Insira a palavra-passe:</p>
                    
                    <?php if (!empty($erro_enigma)): ?>
                        <div class="erro-feedback"><?= htmlspecialchars($erro_enigma) ?></div>
                    <?php endif; ?>

                    <form action="index.php?acao=tentar" method="POST">
                        <input type="hidden" name="alvo" value="celular">
                        <input type="text" name="resposta" placeholder="Digite a palavra..." required autofocus autocomplete="off">
                        <button type="submit">Desbloquear</button>
                    </form>
                </div>
                <a href="index.php?acao=sala&sala=medio1" class="btn-voltar">&lt; Voltar para a sala</a>
            </div>
        <?php endif; ?>

        <!-- Acesso à Porta (Sempre disponível se tiver o código) -->
        <div class="painel-porta">
            <form action="index.php?acao=tentar" method="POST">
                <input type="hidden" name="alvo" value="porta">
                <label>CÓDIGO DE SAÍDA:</label>
                <input type="password" name="resposta" maxlength="5" required>
                <button type="submit">Forçar Porta</button>
            </form>
        </div>
    </div>

    <script src="assets/cronometro.js"></script>
    <script>
        // Inicializa o cronômetro baseado no tempo do servidor
        iniciarCronometro(<?= (int)$partida['limite_segundos'] ?>, <?= (int)$partida['segundos_decorridos'] ?>, <?= (int)$partida['erros'] ?>, 15);
    </script>
</body>
</html>