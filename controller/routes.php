<?php
// Roteador principal: transforma a action da URL em uma operação do sistema.
    require_once __DIR__ . '/cargoController.php';
    require_once __DIR__ . '/depController.php';
    require_once __DIR__ . '/funcController.php';
    require_once __DIR__ . '/contaController.php';
    require_once __DIR__ . '/solicitacaoController.php';
    require_once __DIR__ . '/TokenCsrf.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    // $action = strtolower(trim((string)($_GET['action'] ?? 'apresentacao')));
    $action = trim((string)($_GET['action'] ?? 'apresentacao'));

    // Reservado para futuros nomes alternativos de actions.
    $aliasesAcoes = [];
    if (isset($aliasesAcoes[$action])) {
        $action = $aliasesAcoes[$action];
    }

    $controller = new CargoController();
    $controller2 = new DepController();
    $controller3 = new FuncController();
    $controller4 = new ContaController();
    $controllerSolicitacao = new SolicitacaoController();

    function verificarAdmin() {
        $funcional = (int) ($_SESSION['usuario_funcional'] ?? 0);
        $nivelAcesso = $funcional > 0 ? (new Conta())->buscarNivelAcesso($funcional) : null;

        if ($nivelAcesso === 0) {
            $_SESSION['usuario_nivel'] = $nivelAcesso;
            $_SESSION['usuario_perfil'] = 'admin';
            return;
        }

        if ($nivelAcesso === null) {
            unset($_SESSION['usuario_funcional'], $_SESSION['usuario_nome'], $_SESSION['usuario_nivel'], $_SESSION['usuario_perfil']);
        } else {
            $_SESSION['usuario_nivel'] = $nivelAcesso;
            $_SESSION['usuario_perfil'] = 'user';
        }

        $_SESSION['alerta'] = [
            'icon' => 'error',
            'title' => 'Acesso negado',
            'text' => 'Esta funcionalidade é restrita a administradores.'
        ];
        header('Location: index.php?action=' . ($funcional > 0 && $nivelAcesso !== null ? 'home' : 'login'));
        exit;
    }
    
    function verificarLogado() {
        // Redireciona visitantes não autenticados para o login.
        if (!isset($_SESSION['usuario_funcional'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $nivelAcesso = (new Conta())->buscarNivelAcesso((int) $_SESSION['usuario_funcional']);
        if ($nivelAcesso === null) {
            unset($_SESSION['usuario_funcional'], $_SESSION['usuario_nome'], $_SESSION['usuario_nivel'], $_SESSION['usuario_perfil']);
            header("Location: index.php?action=login");
            exit;
        }

        $_SESSION['usuario_nivel'] = $nivelAcesso;
        $_SESSION['usuario_perfil'] = $nivelAcesso === 0 ? 'admin' : 'user';
    }

    switch ($action) {
        // APRESENTAÇÃO DO PROJETO
        case 'apresentacao':
            include __DIR__."/../view/apresentacao.php";
            break;

        // HOME
        case 'home':
            verificarLogado();
            include __DIR__."/../view/home.php";
            break;

        // CARGOS
        case 'formCargo':
            verificarAdmin();
            include __DIR__."/../view/cargoForm.php";
            break;
        case 'listaCargo':
            verificarAdmin();
            $controller->listar();
            break;
        case 'salvarCargo':
            verificarAdmin();
            $controller->salvar();
            break;
        // Abre, salva ou exclui um cargo existente.
        case 'editarCargo': verificarAdmin(); $controller->editar(); break;
        case 'atualizarCargo': verificarAdmin(); $controller->atualizar(); break;
        case 'excluirCargo':
            verificarAdmin();
            TokenCsrf::exigirPost('listaCargo');
            $controller->excluir();
            break;

        // DEPARTAMENTOS
        case 'formDep':
            verificarAdmin();
            include __DIR__."/../view/depForm.php";
            break;
        case 'listaDep':
            verificarAdmin();
            $controller2->listar();
            break;
        case 'salvarDep':
            verificarAdmin();
            $controller2->salvar();
            break;
        // Abre, salva ou exclui um departamento existente.
        case 'editarDep': verificarAdmin(); $controller2->editar(); break;
        case 'atualizarDep': verificarAdmin(); $controller2->atualizar(); break;
        case 'excluirDep':
            verificarAdmin();
            TokenCsrf::exigirPost('listaDep');
            $controller2->excluir();
            break;

        // FUNCIONÁRIOS
        case 'formFunc': 
            verificarAdmin();
            // carregar models
            require_once __DIR__.'/../model/cargoModel.php';
            require_once __DIR__.'/../model/departamentoModel.php';
            
            // pegar listas
            $cargoModel = new Cargo();
            $listaCargo = $cargoModel->listarTodos();
            
            $depModel = new Departamento();
            $listaDep = $depModel->listarTodos();
            
            // incluir a view
            include __DIR__."/../view/funcForm.php";
            break;
        case 'listaFunc':
            verificarAdmin();
            $controller3->listarTodos();
            break;
        case 'salvarFunc':
            verificarAdmin();
            $controller3->salvar();
            break;
        // Abre, salva ou exclui um funcionário existente.
        case 'editarFunc': verificarAdmin(); $controller3->editar(); break;
        case 'atualizarFunc': verificarAdmin(); $controller3->atualizar(); break;
        case 'excluirFunc':
            verificarAdmin();
            TokenCsrf::exigirPost('listaFunc');
            $controller3->excluir();
            break;

        // CONTAS
        case 'formConta':
            verificarAdmin();
            $controller4->formularioConta();
            break;
        case 'listaConta':
            verificarAdmin();
            $controller4->listarConta();
            break;
        case 'salvarConta':
            verificarAdmin();
            $controller4->salvarConta();
            break;
        // Permite ao administrador editar ou excluir uma conta de acesso.
        case 'editarConta': verificarAdmin(); $controller4->editar(); break;
        case 'atualizarConta': verificarAdmin(); $controller4->atualizar(); break;
        case 'excluirConta':
            verificarAdmin();
            TokenCsrf::exigirPost('listaConta');
            $controller4->excluir();
            break;

        // LOGIN
        case 'login':
            $controller4->abrirLogin();
            break;

        // AUTENTICAÇÃO
        case 'autenticar':
            $controller4->autenticar();
            break;


        // PERFIL DO USUÁRIO
        case 'relatorioPerfil':
            verificarLogado();
            $controller4->relatorioPerfil();
            break;

        case 'atualizarPerfil':
            verificarLogado();
            $controller4->atualizarPerfil();
            break;

        // FÉRIAS E AUSÊNCIAS
        case 'solicitacoes':
            verificarLogado();
            $controllerSolicitacao->listar();
            break;
        case 'novaSolicitacao':
            verificarLogado();
            $controllerSolicitacao->formulario();
            break;
        case 'salvarSolicitacao':
            verificarLogado();
            $controllerSolicitacao->salvar();
            break;
        case 'cancelarSolicitacao':
            verificarLogado();
            $controllerSolicitacao->cancelar();
            break;
        case 'avaliarSolicitacao':
            verificarAdmin();
            $controllerSolicitacao->avaliar();
            break;


        // SAIR DO SISTEMA
        case 'logout':
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $cookie = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httponly']
                );
            }
            session_destroy();
            header('Location: index.php?action=login');
            exit;

        default:
            include __DIR__ . "/../view/apresentacao.php";
            break;
    }
?>
