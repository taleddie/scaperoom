<?php use App\Core\View; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilo.css">
</head>
<body>
    <main>
        <?php // $conteudo e o html da tela (nosso), por isso nao passa pelo e() ?>
        <?= $conteudo ?>
    </main>
</body>
</html>
