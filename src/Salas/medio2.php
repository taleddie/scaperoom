<?php
/** @var array $partida Dados da partida atual vindos do Banco */
/** @var array $etapasDesbloqueadas Estado atual do progresso na sessão */
$etapaMesa = $etapasDesbloqueadas['mesa'] ?? false;
$etapaCelular = $etapasDesbloqueadas['celular'] ?? false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escape Room // Nível Médio (Backrooms)</title>
    <!-- DICA ESCONDIDA NO CÓDIGO-FONTE (Estilo Notpron): 
         "O vinho traz o nome das montanhas, mas as letras baralhadas guardam o animal da floresta: C-A-S-T-R-O" -> Anagrama: CASTOR 
    -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="sala-backrooms">

    <div class="hud-topo">
        <span>SALA: MEDIO1 | TEMPO RESTANTE: <span id="tempo-restante">--:--</span></span>
        <a href="index.php?acao=salas" class="btn-sair">[ Abandonar Sala ]</a>
    </div>

    <div class="ambiente-container">
        <!-- Imagem 1: Visão Geral da Sala -->
        <?php if (!$etapaMesa && !$etapaCelular): ?>
            <div class="interativo-wrapper">
                <img src="assets/img/m1/cenario.jpg" alt="Sala escura com luzes verdes" usemap="#mapa-geral">
                <map name="mapa-geral">
                    <!-- Área clicável da Mesa com o Vinho -->
                    <area shape="rect" coords="400,500,550,650" href="index.php?acao=examinar&alvo=mesa" alt="Mesa de Centro">
                    <!-- Área clicável do Balcão/Fogão com o Celular -->
                    <area shape="rect" coords="700,450,850,600" href="index.php?acao=examinar&alvo=celular" alt="Balcão e Celular">
                </map>
            </div>
        <?php endif; ?>

        <!-- Imagem 2: Close na Mesa / Vinho Alamos -->
        <?php if ($etapaMesa && !$etapaCelular): ?>
            <div class="zoom-objeto">
                <img src="assets/img/m1/vinho.jpg" alt="Garrafa de Vinho Alamos sobre a mesa">
                <p class="texto-ambiente">A garrafa de vinho repousa sobre a mesa de centro. O rótulo destaca: <strong>ALAMOS CABERNET SAUVIGNON</strong>.</p>
                <!-- Dica no atributo title ou comentário HTML escondido -->
                <p class="dica-invisivel" style="color: #1a170c; font-size: 1px;">Pista: reorganice as letras da vinícola ou do vale.</p>
                <a href="index.php?acao=sala&sala=medio1" class="btn-voltar">&lt; Voltar para a sala</a>
            </div>
        <?php endif; ?>

        <!-- Imagem 3: Close no Balcão / Celular Iluminado -->
        <?php if ($etapaCelular): ?>
            <div class="zoom-objeto">
                <img src="assets/img/m1/celular.jpg" alt="Celular brilhando no balcão ao lado do fogão">
                <div class="interface-celular">
                    <h3>DISPOSITIVO BLOQUEADO</h3>
                    <p>Insira a palavra-passe (o anagrama descoberto):</p>
                    
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
                <label>CÓDIGO DE SAÍDA (5 DÍGITOS):</label>
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