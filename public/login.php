<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\DAO\JogadorDAO;

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        $dao = new JogadorDAO();

        if ($acao === 'cadastro') {
            $nome  = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if ($nome === '' || $email === '' || $senha === '') {
                $erro = 'REGISTO INCOMPLETO. TODOS OS DADOS SÃO NECESSÁRIOS.';
            } elseif ($dao->emailExiste($email)) {
                $erro = 'EMAIL JÁ REGISTRADO.';
            } else {
                $jogador = $dao->criar($nome, $email, $senha);
                $_SESSION['jogador'] = [
                    'id'    => $jogador->getId(),
                    'nome'  => $jogador->getNome(),
                    'email' => $jogador->getEmail(),
                ];
                $sucesso = "ENTRADA REGISTADA. NÍVEL CEDIDO: #{$jogador->getId()}";
            }
        } elseif ($acao === 'login') {
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            $jogador = $dao->autenticar($email, $senha);

            if ($jogador !== null) {
                $_SESSION['jogador'] = [
                    'id'    => $jogador->getId(),
                    'nome'  => $jogador->getNome(),
                    'email' => $jogador->getEmail(),
                ];
                $sucesso = "SINAL RECONHECIDO. BEM-VINDO AO JOGO, " . strtoupper($jogador->getNome());
            } else {
                $erro = 'ACESSO NEGADO. EMAIL OU SENHA INCORRETOS.';
            }
        } elseif ($acao === 'logout') {
            unset($_SESSION['jogador']);
            session_destroy();
            $sucesso = 'LOGOUT BEM-SUCESSIDO.';
        }
    } catch (Throwable $e) {
        $erro = 'FALHA NA ANOMALIA: ' . $e->getMessage();
    }
}

$logado = $_SESSION['jogador'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BACKROOMS // REGISTO DE SOBREVIVÊNCIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=VT323&family=Special+Elite&display=swap');

        body {
            background-color: #1a170c;
            color: #d4d0c7;
            font-family: 'Special Elite', monospace;
            overflow-x: hidden;
        }

        /* Efeito de fita VHS antiga com chuvisco subtil */
        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.35) 50%), linear-gradient(90deg, rgba(255, 200, 0, 0.02), rgba(0, 0, 0, 0.05), rgba(255, 200, 0, 0.02));
            z-index: 9999;
            background-size: 100% 3px, 4px 100%;
            pointer-events: none;
        }

        .backrooms-card {
            background: #11100a;
            border: 2px solid #3d351c;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.9), inset 0 0 15px rgba(255, 180, 0, 0.03);
            position: relative;
        }

        .liminal-title {
            font-family: 'VT323', monospace;
            letter-spacing: 3px;
            color: #d9c577;
            text-shadow: 2px 2px 0px rgba(0,0,0,0.9);
        }

        input {
            background: #080705 !important;
            color: #f3f0e6 !important;
            border: 1px solid #4a3f22 !important;
            font-family: 'Special Elite', monospace;
        }

        input:focus {
            border-color: #b59b41 !important;
            outline: none;
            box-shadow: 0 0 8px rgba(181, 155, 65, 0.2);
        }

        .vhs-time {
            font-family: 'VT323', monospace;
            letter-spacing: 2px;
            color: #b59b41;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative">

    <!-- Indicador de gravação de câmara analógica com relógio em tempo real -->
    <div id="relogio-sistema" class="absolute top-4 left-4 vhs-time text-2xl opacity-75">
        [SP] --/--/-- --:--:-- 
    </div>

    <div class="w-full max-w-md backrooms-card rounded p-8">
        
        <div class="text-center mb-6 border-b border-[#2d2716] pb-4">
            <span class="text-[10px] tracking-widest text-[#8c7d53] uppercase block mb-1">
                REGISTO DE PERDIDOS
            </span>
            <h1 class="text-4xl font-bold liminal-title mt-2">
                noem dojofo
            </h1>
            <p class="text-xs text-[#7a6b46] mt-1 uppercase">Identifique-se antes que algo te encontre.</p>
        </div>

        <?php if ($erro): ?>
            <div class="mb-5 p-3 rounded bg-[#361212] border border-[#6b2222] text-[#f87171] text-xs tracking-wider font-bold">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="mb-5 p-3 rounded bg-[#16291a] border border-[#2b5432] text-[#86efac] text-xs tracking-wider">
                ✓ <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <?php if ($logado): ?>
            <div class="bg-[#0c0b08] border border-[#3d351c] rounded p-4 space-y-3 mb-6">
                <div class="flex items-center justify-between text-xs text-[#8c7d53] border-b border-[#2d2716] pb-2">
                    <span>ESTADO: NA SALA</span>
                    <span class="font-mono">ID: #<?= (int)$logado['id'] ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-[#7a6b46] block upperca:</span>
                    <span class="font-bold text-lg text-[#f3f0e6]"><?= htmlspecialchars($logado['nome']) ?></span>
                </div>
                <div>
                    <span class="text-[10px] text-[#7a6b46] block uppercase">EMAIL:</span>
                    <span class="text-xs text-[#b59b41]"><?= htmlspecialchars($logado['email']) ?></span>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="acao" value="logout">
                <button type="submit" class="w-full bg-[#261515] hover:bg-[#381a1a] border border-[#542b2b] text-[#f87171] font-bold py-2.5 rounded text-xs transition tracking-widest cursor-pointer uppercase">
                    [ ABANDONAR SALA ]
                </button>
            </form>
        <?php else: ?>
            <div class="grid grid-cols-2 border-b border-[#2d2716] mb-6 text-sm">
                <button type="button" id="btn-login" onclick="mudarAba('login')"
                    class="pb-2 font-bold border-b-2 border-[#b59b41] text-[#d9c577] transition cursor-pointer tracking-wider">
                    [ ENTRAR ]
                </button>
                <button type="button" id="btn-cadastro" onclick="mudarAba('cadastro')"
                    class="pb-2 font-bold border-b-2 border-transparent text-[#6e603d] hover:text-[#b59b41] transition cursor-pointer tracking-wider">
                    [ REGISTAR ]
                </button>
            </div>

            <!-- Login Form -->
            <form id="form-login" method="POST" class="space-y-4">
                <input type="hidden" name="acao" value="login">
                <div>
                    <label class="block text-xs uppercase text-[#8c7d53] mb-1">&gt; EMAIL:</label>
                    <input type="email" name="email" required placeholder="ex: salominho@gmail.com"
                        class="w-full rounded px-3.5 py-2 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#8c7d53] mb-1">&gt; PALAVRA-PASSE:</label>
                    <input type="password" name="senha" required placeholder="••••••••"
                        class="w-full rounded px-3.5 py-2 text-sm focus:outline-none">
                </div>
                <button type="submit"
                    class="w-full bg-[#1e1a0f] hover:bg-[#2c2617] border border-[#6b5828] text-[#d9c577] font-bold py-2.5 rounded text-xs transition tracking-widest cursor-pointer uppercase shadow-lg">
                    [ RETOMAR JOGO ]
                </button>
            </form>

            <!-- Cadastro Form -->
            <form id="form-cadastro" method="POST" class="space-y-4 hidden">
                <input type="hidden" name="acao" value="cadastro">
                <div>
                    <label class="block text-xs uppercase text-[#8c7d53] mb-1">&gt; NOME:</label>
                    <input type="text" name="nome" maxlength="80" required placeholder="Ex: Salomão"
                        class="w-full rounded px-3.5 py-2 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#8c7d53] mb-1">&gt; EMAIL:</label>
                    <input type="email" name="email" maxlength="100" required placeholder="ex: salominho@gmail.net"
                        class="w-full rounded px-3.5 py-2 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs uppercase text-[#8c7d53] mb-1">&gt; CRIAR PALAVRA-PASSE:</label>
                    <input type="password" name="senha" required placeholder="••••••••"
                        class="w-full rounded px-3.5 py-2 text-sm focus:outline-none">
                </div>
                <button type="submit"
                    class="w-full bg-[#1e1a0f] hover:bg-[#2c2617] border border-[#6b5828] text-[#d9c577] font-bold py-2.5 rounded text-xs transition tracking-widest cursor-pointer uppercase shadow-lg">
                    [ ENTRAR NO LABIRINTO ]
                </button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function mudarAba(aba) {
            const formLogin = document.getElementById('form-login');
            const formCadastro = document.getElementById('form-cadastro');
            const btnLogin = document.getElementById('btn-login');
            const btnCadastro = document.getElementById('btn-cadastro');

            if (aba === 'login') {
                formLogin.classList.remove('hidden');
                formCadastro.classList.add('hidden');
                btnLogin.className = 'pb-2 font-bold border-b-2 border-[#b59b41] text-[#d9c577] transition cursor-pointer tracking-wider';
                btnCadastro.className = 'pb-2 font-bold border-b-2 border-transparent text-[#6e603d] hover:text-[#b59b41] transition cursor-pointer tracking-wider';
            } else {
                formLogin.classList.add('hidden');
                formCadastro.classList.remove('hidden');
                btnCadastro.className = 'pb-2 font-bold border-b-2 border-[#b59b41] text-[#d9c577] transition cursor-pointer tracking-wider';
                btnLogin.className = 'pb-2 font-bold border-b-2 border-transparent text-[#6e603d] hover:text-[#b59b41] transition cursor-pointer tracking-wider';
            }
        }

        // Relógio dinâmico em tempo real da máquina
        function atualizarRelogio() {
            const agora = new Date();
            const mes = String(agora.getMonth() + 1).padStart(2, '0');
            const dia = String(agora.getDate()).padStart(2, '0');
            const ano = String(agora.getFullYear()).slice(-2);
            
            const horas = String(agora.getHours()).padStart(2, '0');
            const minutos = String(agora.getMinutes()).padStart(2, '0');
            const segundos = String(agora.getSeconds()).padStart(2, '0');

            document.getElementById('relogio-sistema').innerText = `[SP] ${mes}/${dia}/${ano} - ${horas}:${minutos}:${segundos}`;
        }

        setInterval(atualizarRelogio, 1000);
        atualizarRelogio();
    </script>
</body>
</html>