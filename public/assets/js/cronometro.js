(function () {
    var el = document.getElementById('cronometro');
    if (!el) return;

    var restante = parseInt(el.getAttribute('data-restante'), 10);
    var fim = Date.now() + restante * 1000;
    var timer;

    function desenhar() {
        var s = Math.max(0, Math.ceil((fim - Date.now()) / 1000));
        var mm = String(Math.floor(s / 60)).padStart(2, '0');
        var ss = String(s % 60).padStart(2, '0');
        el.textContent = mm + ':' + ss;
        el.classList.toggle('urgente', s <= 60);

        if (s <= 0) {
            clearInterval(timer);
            window.location.href = 'index.php?acao=verificar_tempo';
        }
    }

    timer = setInterval(desenhar, 250);
    desenhar();
})();
