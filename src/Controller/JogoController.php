public function examinar(): void {
    Sessao::exigirLogin();
    $alvo = filter_input(INPUT_GET, 'alvo', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // O servidor controla estritamente o fluxo de etapas
    if ($alvo === 'mesa') {
        $_SESSION['etapas_medio1']['mesa'] = true;
    } elseif ($alvo === 'celular') {
        // Regra de segurança: só pode acessar o celular se já passou pela mesa/pista
        if (empty($_SESSION['etapas_medio1']['mesa'])) {
            header('Location: index.php?acao=sala&sala=medio1&erro=etapa_bloqueada');
            exit;
        }
        $_SESSION['etapas_medio1']['celular'] = true;
    }

    header('Location: index.php?acao=sala&sala=medio1');
    exit;
}

public function tentar(): void {
    Sessao::exigirLogin();
    $alvo = filter_input(INPUT_POST, 'alvo', FILTER_SANITIZE_SPECIAL_CHARS);
    $resposta = trim(filter_input(INPUT_POST, 'resposta', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    $partidaId = $_SESSION['partida_id'] ?? null;
    $partidaDAO = new PartidaDAO();
    $partida = $partidaDAO->buscarPorId($partidaId);

    if (!$partida || $partida['status'] !== 'em_andamento') {
        header('Location: index.php?acao=salas');
        exit;
    }

    $sucesso = false;

    if ($alvo === 'celular') {
        // Resposta esperada: 'castor' (ignorando maiúsculas/espaços conforme regra do EnigmaCodigo)
        if (strcasecmp($resposta, 'castor') === 0) {
            $_SESSION['etapas_medio1']['celular_resolvido'] = true;
            $sucesso = true;
        }
    } elseif ($alvo === 'porta') {
        // Regra: Só pode abrir a porta se resolveu o celular antes
        if (empty($_SESSION['etapas_medio1']['celular_resolvido'])) {
            header('Location: index.php?acao=sala&sala=medio1&erro=celular_pendente');
            exit;
        }

        // Código final gerado pela senha do celular (ex: '48192')
        if ($resposta === '48192') {
            $partidaDAO->finalizar($partidaId, 'concluida');
            header('Location: index.php?acao=vitoria');
            exit;
        }
    }

    // Se errou, aplica a penalidade de tempo registrada no banco (-15s)[cite: 2]
    if (!$sucesso) {
        $partidaDAO->registrarErro($partidaId);
        // Redireciona de volta com aviso de erro
        header('Location: index.php?acao=sala&sala=medio1&erro=incorreto');
        exit;
    }

    header('Location: index.php?acao=sala&sala=medio1');
    exit;
}