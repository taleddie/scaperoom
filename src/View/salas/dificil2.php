<?php
use App\Core\View;

$base  = 'index.php?acao=';
$idUrl = $base . 'sala&amp;sala=' . View::e($sala['id']);
$img   = 'assets/img/dificil2/';

// quando a gaveta abre, o manual aparece na mesa (troca as imagens do escritorio)
$comDoc   = !empty($etapas['gaveta_aberta']);
$cenaBase = $img . ($comDoc ? 'Pizzaria_comDOC.png' : 'Pizzaria_semDOC.png');
$cenaLuz  = $img . ($comDoc ? 'Pizzaria_comDOC_luz.png' : 'Pizzaria_semDOC_luz.jpg');

// botoes do mapa de cameras SEM sinal: nome => [esquerda %, topo %, largura %, altura %]
// o mapa fica em posicoes diferentes em Camera.png e Camera2.png, por isso sao dois conjuntos
$semSinalCamera = [
    '1B' => [74.6, 56.9, 4.7, 5.1],
    '1C' => [72.2, 67.0, 4.6, 5.1],
    '7'  => [91.7, 60.5, 4.5, 5.1],
    '3'  => [70.0, 79.6, 4.7, 5.2],
    '2A' => [76.1, 82.0, 4.7, 5.1],
    '2B' => [76.1, 86.8, 4.7, 5.1],
    '4A' => [83.8, 82.1, 4.7, 5.2],
    '4B' => [83.8, 87.5, 4.7, 5.1],
    '6'  => [91.0, 77.4, 4.6, 5.2],
];
$semSinalCam5 = [
    '1B' => [74.3, 53.6, 5.0, 5.5],
    '1C' => [71.9, 64.7, 4.9, 5.7],
    '7'  => [92.2, 57.1, 5.0, 5.7],
    '3'  => [69.4, 78.9, 4.9, 5.6],
    '2A' => [75.7, 81.2, 5.1, 5.4],
    '2B' => [75.7, 87.1, 4.9, 5.5],
    '4A' => [83.9, 81.2, 4.9, 5.4],
    '4B' => [83.9, 87.1, 4.9, 5.5],
    '6'  => [91.4, 75.9, 5.0, 5.5],
];
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

<!-- escritorio (cenario principal) -->
<?php if ($vista === 'cenario'): ?>
    <div class="cena">
        <img id="cena-img" src="<?= $cenaBase ?>" alt="Escritório do segurança, com um telefone sobre a mesa e uma porta de cada lado">

        <!-- telefone -->
        <a class="ponto" style="left:26.6%;top:53%;width:21.2%;height:20.7%"
            href="<?= $base ?>examinar&amp;objeto=telefone" title="Telefone"></a>

        <!-- gaveta da bancada -->
        <a class="ponto" style="left:29%;top:43%;width:17%;height:9%"
            href="<?= $base ?>examinar&amp;objeto=gaveta" title="Gaveta"></a>

        <!-- computador (monitores no fundo da bancada) -->
        <a class="ponto" style="left:57.5%;top:24%;width:10.5%;height:16%"
            href="<?= $base ?>examinar&amp;objeto=computador" title="Computador"></a>

        <!-- botao das cameras -->
        <a class="ponto" style="left:32%;top:92%;width:36%;height:7%"
            href="<?= $base ?>examinar&amp;objeto=camera" title="Câmeras"></a>

        <!-- botao da luz da porta esquerda: pisca em morse (so no navegador, script no fim) -->
        <a class="ponto" id="luz-esq" data-luz="<?= $cenaLuz ?>" href="#"
            style="left:1%;top:36.5%;width:5%;height:14.5%" title="Luz da porta esquerda"></a>
    </div>

<!-- telefone: recado gravado -->
<?php elseif ($vista === 'telefone'): ?>
    <div class="cena">
        <img src="<?= $cenaBase ?>" alt="Telefone sobre a mesa do escritório">
    </div>

    <div class="achado">
        <p>O telefone toca. É uma mensagem gravada:</p>
        <p>
            “Quando você terminar seu expediente, e o pessoal da manhã chegar, avisa eles que a ordem
            pra ligar os animatronics é Chica, Freddy e Bonnie... Ah, e tem um manual na gaveta da bancada.”
        </p>
        <p>
            “E não esquece: pra bater o ponto e ir embora, você precisa digitar o número do seu
            cartão de acesso no computador.”
        </p>
    </div>

    <p><a class="botao" href="<?= $idUrl ?>">Voltar</a></p>

<!-- gaveta: cadeado de 3 digitos -->
<?php elseif ($vista === 'gaveta'): ?>
    <div class="cena">
        <img src="<?= $cenaBase ?>" alt="Bancada com uma gaveta trancada">
    </div>

    <?php if (!$comDoc): ?>
        <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
            <?= View::csrf() ?>
            <input type="hidden" name="alvo" value="gaveta">
            <label>A gaveta tem um cadeado (3 dígitos)
                <input type="text" name="resposta" maxlength="3" inputmode="numeric" pattern="[0-9]{3}" required autofocus>
            </label>
            <button type="submit">Abrir</button>
        </form>
    <?php else: ?>
        <div class="achado">
            <p>Você achou um documento: o manual do segurança. Ele agora está sobre a mesa.</p>
        </div>
    <?php endif; ?>

    <p><a class="botao" href="<?= $idUrl ?>">Voltar</a></p>

<!-- monitor de cameras: CAM 1A (Camera.png) -->
<?php elseif ($vista === 'camera'): ?>
    <div class="cena">
        <img src="<?= $img ?>Camera.png" alt="Câmera do palco, com três animatrônicos">

        <!-- CAM 5: tem sinal, mas pede senha -->
        <a class="ponto" style="left:66.9%;top:60.3%;width:4.6%;height:5.1%"
            href="<?= $base ?>examinar&amp;objeto=cam5" title="CAM 5"></a>

        <!-- as outras cameras estao fora do ar -->
        <?php foreach ($semSinalCamera as $nome => [$x, $y, $w, $h]): ?>
            <a class="ponto sem-sinal" href="#" title="CAM <?= $nome ?>"
                style="left:<?= $x ?>%;top:<?= $y ?>%;width:<?= $w ?>%;height:<?= $h ?>%"></a>
        <?php endforeach; ?>

        <!-- barra de baixo: fecha o monitor -->
        <a class="ponto" style="left:24.3%;top:88.8%;width:42.5%;height:5.6%"
            href="<?= $idUrl ?>" title="Fechar câmeras"></a>
    </div>

    <p class="mensagem" id="aviso-cam" hidden>Câmera indisponível.</p>
    <p class="legenda">Câmeras com sinal: CAM 1A e CAM 5. As outras estão fora do ar.</p>
    <p><a class="botao" href="<?= $idUrl ?>">Fechar câmeras</a></p>

<!-- CAM 5: pede senha; depois de liberada mostra o poster (Camera2.png) -->
<?php elseif ($vista === 'cam5'): ?>
    <?php if (empty($etapas['cam5_liberada'])): ?>
        <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
            <?= View::csrf() ?>
            <input type="hidden" name="alvo" value="cam5">
            <label>CAM 5: acesso restrito. Digite a senha da câmera
                <input type="text" name="resposta" maxlength="10" required autofocus>
            </label>
            <button type="submit">Desbloquear</button>
        </form>

        <p><a class="botao" href="<?= $idUrl ?>&amp;vista=camera">Voltar às câmeras</a></p>
    <?php else: ?>
        <div class="cena">
            <img src="<?= $img ?>Camera2.png" alt="Pôster na parede perguntando se você sabe ler e contar">

            <!-- CAM 1A: volta pro palco -->
            <a class="ponto" style="left:76.1%;top:45.6%;width:5%;height:5.5%"
                href="<?= $idUrl ?>&amp;vista=camera" title="CAM 1A"></a>

            <?php foreach ($semSinalCam5 as $nome => [$x, $y, $w, $h]): ?>
                <a class="ponto sem-sinal" href="#" title="CAM <?= $nome ?>"
                    style="left:<?= $x ?>%;top:<?= $y ?>%;width:<?= $w ?>%;height:<?= $h ?>%"></a>
            <?php endforeach; ?>

            <!-- barra de baixo: fecha o monitor -->
            <a class="ponto" style="left:20.6%;top:90.9%;width:46.1%;height:5.3%"
                href="<?= $idUrl ?>" title="Fechar câmeras"></a>
        </div>

        <p class="mensagem" id="aviso-cam" hidden>Câmera indisponível.</p>
        <p class="legenda">Câmeras com sinal: CAM 1A e CAM 5. As outras estão fora do ar.</p>
        <p><a class="botao" href="<?= $idUrl ?>">Fechar câmeras</a></p>
    <?php endif; ?>

<!-- computador: numero do cartao de acesso -->
<?php elseif ($vista === 'computador'): ?>
    <div class="cena">
        <img src="<?= $cenaBase ?>" alt="Monitores sobre a bancada">
    </div>

    <form method="post" action="<?= $base ?>tentar" class="caixa" autocomplete="off">
        <?= View::csrf() ?>
        <input type="hidden" name="alvo" value="computador">
        <label>TERMINAL DE PONTO: número do cartão de acesso (5 dígitos)
            <input type="text" name="resposta" maxlength="5" inputmode="numeric" pattern="[0-9]{5}" required autofocus>
        </label>
        <button type="submit">Bater ponto</button>
    </form>

    <p><a class="botao" href="<?= $idUrl ?>">Voltar</a></p>
<?php endif; ?>

<script src="assets/js/cronometro.js"></script>
<script>
// cameras sem sinal: so mostra o aviso (nao vai ao servidor)
(function () {
    var aviso = document.getElementById('aviso-cam');
    document.querySelectorAll('.sem-sinal').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            aviso.hidden = false;
            clearTimeout(aviso.timer);
            aviso.timer = setTimeout(function () { aviso.hidden = true; }, 2500);
        });
    });
})();

// luz da porta esquerda: pisca "FOX" em morse trocando a imagem (so no navegador)
(function () {
    var luz = document.getElementById('luz-esq');
    if (!luz) return;

    var img     = document.getElementById('cena-img');
    var apagada = img.getAttribute('src');
    var acesa   = luz.getAttribute('data-luz');
    var morse   = '..-. --- -..-';   // FOX. se mudar a palavra no catalogo, mude aqui tambem
    var U       = 250;               // duracao de um ponto, em ms
    var tocando = false;

    new Image().src = acesa;         // pre-carrega, senao o pisca falha na primeira vez

    luz.addEventListener('click', function (e) {
        e.preventDefault();
        if (tocando) return;
        tocando = true;

        // fila de passos: [luz acesa?, duracao]
        var passos = [[false, U * 2]];
        morse.split('').forEach(function (c) {
            if (c === '.')      passos.push([true, U], [false, U]);
            else if (c === '-') passos.push([true, U * 3], [false, U]);
            else                passos.push([false, U * 2]);   // pausa entre letras
        });

        (function passo(i) {
            if (i >= passos.length) { img.src = apagada; tocando = false; return; }
            img.src = passos[i][0] ? acesa : apagada;
            setTimeout(function () { passo(i + 1); }, passos[i][1]);
        })(0);
    });
})();
</script>