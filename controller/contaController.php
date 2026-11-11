<?php
// Controller de login, contas administrativas e perfil do usuário.
require_once __DIR__ . '/../model/contaModel.php';
require_once __DIR__ . '/../model/funcionarioModel.php';
require_once __DIR__ . '/UploadImagem.php';

class ContaController {

    public function abrirLogin() {
        // Exibe o formulário de autenticação.
        include __DIR__ . "/../view/login.php";
    }

    public function autenticar() {
        // Valida credenciais e cria as variáveis da sessão.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }

        $login = trim($_POST['login'] ?? '');
        $senha = (string) ($_POST['senha'] ?? '');

        if ($login === '' || $senha === '') {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Campos obrigatórios',
                'text' => 'Preencha login e senha.'
            ];
            header('Location: index.php?action=login');
            exit;
        }

        $modelConta = new Conta();
        try {
            $conta = $modelConta->buscarPorLogin($login);
        } catch (PDOException $e) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Erro ao acessar o sistema',
                'text' => 'Não foi possível validar o login. Tente novamente.'
            ];
            header('Location: index.php?action=login');
            exit;
        }
        $senhaValida = $conta && password_verify($senha, $conta['senha']);

        if ($conta && $senhaValida) {
            session_regenerate_id(true);
            $_SESSION['usuario_funcional'] = $conta['funcional'];
            $_SESSION['usuario_nome'] = $conta['nome'];
            $_SESSION['usuario_nivel'] = (int) $conta['nivelAcesso'];
            $_SESSION['usuario_perfil'] = isset($conta['nivelAcesso']) && $conta['nivelAcesso'] == 0 ? 'admin' : 'user';

            header('Location: index.php?action=home');
            exit;
        }

        $_SESSION['alerta'] = [
            'icon' => 'error',
            'title' => 'Login inválido',
            'text' => 'Login ou senha inválidos!'
        ];
        header('Location: index.php?action=login');
        exit;
    }

    public function salvarConta() {
        // Valida a seleção administrativa e cria a conta de acesso.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=listaConta');
            exit;
        }

        $funcional = filter_var($_POST['funcional'] ?? null, FILTER_VALIDATE_INT);
        $login = trim((string) ($_POST['login'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $nivelAcesso = filter_var($_POST['nivelAcesso'] ?? null, FILTER_VALIDATE_INT);
        if ($funcional === false || $funcional < 1
            || !in_array($nivelAcesso, [0, 1], true)
            || $login === '' || strlen($login) > 100
            || strlen($senha) < 8 || strlen($senha) > 72) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Confira os dados',
                'text' => 'Selecione um funcionário, informe login e senha válidos e escolha um nível permitido.'
            ];
            header('Location: index.php?action=formConta');
            exit;
        }

        $modelConta = new Conta();
        $funcionarioValido = $modelConta->buscarFuncionarioSemConta($funcional);
        if (!$funcionarioValido) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Funcionário indisponível',
                'text' => 'Selecione um funcionário cadastrado que ainda não possua conta.'
            ];
            header('Location: index.php?action=formConta');
            exit;
        }

        $diretorioFotos = __DIR__ . '/../assets/img/perfil/';
        $fotoEnviada = null;
        try {
            $fotoEnviada = UploadImagem::salvarOpcional('foto', $diretorioFotos);
        } catch (RuntimeException $erro) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Foto inválida',
                'text' => $erro->getMessage()
            ];
            header('Location: index.php?action=formConta');
            exit;
        }

        $nomeFoto = $fotoEnviada ?? 'default.jpg';
        try {
            $sucesso = $modelConta->inserir($funcional, $login, $senha, $nomeFoto, $nivelAcesso);
        } catch (PDOException $erro) {
            $this->removerFotoTemporaria($diretorioFotos, $fotoEnviada);
            if (($erro->errorInfo[0] ?? (string) $erro->getCode()) === '23000'
                && (int) ($erro->errorInfo[1] ?? 0) === 1062) {
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Conta já cadastrada',
                    'text' => 'Este login ou funcionário já possui uma conta.'
                ];
                header('Location: index.php?action=formConta');
                exit;
            }
            error_log($erro->getMessage());
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Erro ao criar conta',
                'text' => 'Não foi possível concluir o cadastro. Tente novamente.'
            ];
            header('Location: index.php?action=formConta');
            exit;
        }

        if ($sucesso) {
            $_SESSION['alerta'] = [
                'icon' => 'success',
                'title' => 'Conta criada!',
                'text' => 'Conta criada com sucesso para ' . $funcionarioValido['nome'] . '!'
            ];
            $destino = 'listaConta';
        } else {
            $this->removerFotoTemporaria($diretorioFotos, $fotoEnviada);
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Não foi possível criar a conta',
                'text' => 'O cadastro não foi concluído. Tente novamente.'
            ];
            $destino = 'formConta';
        }
        header('Location: index.php?action=' . $destino);
        exit;
    }   

    public function formularioConta() {
        $funcionariosDisponiveis = (new Conta())->listarFuncionariosSemConta();
        include __DIR__ . '/../view/contaForm.php';
    }
    
    public function listarConta() {
        // Envia as contas cadastradas para a tabela administrativa.
        $model = new Conta();
        $listaContas = $model->listarConta();
        $temOutroAdministrador = $model->existeAdministradorDiferente($_SESSION['usuario_funcional'] ?? 0);

        include __DIR__ . "/../view/contaLista.php";
    }

    public function editar() {
        // Carrega uma conta para edição pelo administrador.
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $conta = $id !== false && $id > 0 ? (new Conta())->buscarPorId($id) : false;
        if (!$conta) { header('Location: index.php?action=listaConta'); exit; }
        $editandoPropriaConta = (int) $conta['funcional'] === (int) ($_SESSION['usuario_funcional'] ?? 0);
        include __DIR__ . "/../view/contaEditar.php";
    }

    public function atualizar() {
        // Atualiza login, nível e senha opcionalmente.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=listaConta');
            exit;
        }

        $funcional = filter_var($_POST['funcional'] ?? null, FILTER_VALIDATE_INT);
        $nivelAcesso = filter_var($_POST['nivelAcesso'] ?? null, FILTER_VALIDATE_INT);
        $login = trim((string) ($_POST['login'] ?? ''));
        $senhaInformada = (string) ($_POST['senha'] ?? '');
        if ($funcional === false || $funcional < 1
            || !in_array($nivelAcesso, [0, 1], true)
            || $login === '' || strlen($login) > 100
            || ($senhaInformada !== '' && (strlen($senhaInformada) < 8 || strlen($senhaInformada) > 72))) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Dados inválidos',
                'text' => 'Informe um login válido e um nível permitido. A senha opcional deve ter entre 8 e 72 caracteres.'
            ];
            header('Location: index.php?action=' . ($funcional !== false && $funcional > 0 ? 'editarConta&id=' . $funcional : 'listaConta'));
            exit;
        }

        $modelConta = new Conta();
        if (!$modelConta->buscarPorId($funcional)) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Conta não encontrada',
                'text' => 'A conta selecionada não existe mais.'
            ];
            header('Location: index.php?action=listaConta');
            exit;
        }
        $ehPropriaConta = (int) $funcional === (int) ($_SESSION['usuario_funcional'] ?? 0);
        $senha = $ehPropriaConta || $senhaInformada === '' ? null : $senhaInformada;
        $fotoAnterior = $modelConta->buscarPorId($funcional)['foto'] ?? null;
        $diretorioFotos = __DIR__ . '/../assets/img/perfil/';
        try {
            $fotoNova = UploadImagem::salvarOpcional('foto', $diretorioFotos);
        } catch (RuntimeException $erro) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Foto inválida',
                'text' => $erro->getMessage()
            ];
            header('Location: index.php?action=editarConta&id=' . $funcional);
            exit;
        }

        try {
            $sucesso = $modelConta->atualizar($funcional, $login, $nivelAcesso, $senha, $fotoNova);
        } catch (DomainException $erro) {
            $this->removerFotoTemporaria($diretorioFotos, $fotoNova);
            $_SESSION['alerta'] = [
                'icon' => 'warning',
                'title' => 'Ação não permitida',
                'text' => $erro->getMessage()
            ];
            header('Location: index.php?action=editarConta&id=' . $funcional);
            exit;
        } catch (PDOException $erro) {
            $this->removerFotoTemporaria($diretorioFotos, $fotoNova);
            if (($erro->errorInfo[0] ?? (string) $erro->getCode()) === '23000'
                && (int) ($erro->errorInfo[1] ?? 0) === 1062) {
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Login já cadastrado',
                    'text' => 'Escolha um login que ainda não esteja sendo usado.'
                ];
                header('Location: index.php?action=editarConta&id=' . $funcional);
                exit;
            }
            error_log($erro->getMessage());
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Erro ao atualizar conta',
                'text' => 'Não foi possível salvar as alterações. Tente novamente.'
            ];
            header('Location: index.php?action=editarConta&id=' . $funcional);
            exit;
        }
        if (!$sucesso) {
            $this->removerFotoTemporaria($diretorioFotos, $fotoNova);
        } elseif ($fotoNova !== null) {
            $this->removerFotoAntiga($diretorioFotos, $fotoAnterior);
        }
        // Guarda o resultado para o cabeçalho exibir após o redirect.
        $_SESSION['alerta'] = [
            'icon' => $sucesso ? 'success' : 'error',
            'title' => $sucesso ? 'Conta atualizada!' : 'Erro ao atualizar',
            'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar a conta.'
        ];
        header('Location: index.php?action=listaConta');
        exit;
    }

    public function excluir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=listaConta');
            exit;
        }

        // Impede que o sistema fique sem administrador após uma autoexclusão.
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $funcionalAtual = (int) ($_SESSION['usuario_funcional'] ?? 0);
        $ehPropriaConta = $id !== false && $id === $funcionalAtual;
        $model = new Conta();

        if ($id === false || $id < 1) {
            $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Conta inválida', 'text' => 'Não foi possível identificar a conta.'];
            header('Location: index.php?action=listaConta');
            exit;
        }

        if ($ehPropriaConta && !$model->existeAdministradorDiferente($funcionalAtual)) {
            $_SESSION['alerta'] = ['icon' => 'warning', 'title' => 'Ação não permitida', 'text' => 'Cadastre ou promova outro administrador antes de excluir esta conta.'];
            header('Location: index.php?action=listaConta');
            exit;
        }

        try {
            $excluida = $model->deletar($id);
        } catch (DomainException $erro) {
            $_SESSION['alerta'] = [
                'icon' => 'warning',
                'title' => 'Ação não permitida',
                'text' => $erro->getMessage()
            ];
            header('Location: index.php?action=listaConta');
            exit;
        }

        if ($excluida) {
            if ($ehPropriaConta) {
                session_unset();
                session_destroy();
                header('Location: index.php?action=apresentacao');
                exit;
            }
            // Informa à lista que a exclusão foi concluída.
            $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Conta excluída!', 'text' => 'O registro foi removido.'];
        } else {
            $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Erro ao excluir', 'text' => 'Não foi possível excluir a conta.'];
        }
        header('Location: index.php?action=listaConta'); exit;
    }

    public function relatorioPerfil() {
        // Consulta e exibe os dados do perfil autenticado.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_funcional'])) {
            header("Location: index.php?action=login");
            exit;
        }

        try {
            $modelFunc = new Funcionario();
            $funcionario = $modelFunc->buscarPorId($_SESSION['usuario_funcional']);
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            http_response_code(500);
            echo '<h2>Erro ao carregar o perfil</h2>';
            echo '<p>Não foi possível consultar os dados do perfil.</p>';
            exit;
        }

        if (!$funcionario) {
            http_response_code(404);
            echo '<h2>Perfil não encontrado</h2>';
            echo '<p>Não existem dados cadastrais para este usuário.</p>';
            exit;
        }

        include __DIR__ . "/../view/meuPerfil.php";
    }


    public function atualizarPerfil() {
        // Atualiza telefone, foto e senha do próprio usuário.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->relatorioPerfil();
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_funcional'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $funcional = $_SESSION['usuario_funcional'];
        $telefone  = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
        if ($telefone === '' || !in_array(strlen($telefone), [10, 11], true)) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Telefone inválido',
                'text' => 'Informe um telefone com DDD.'
            ];
            header('Location: index.php?action=relatorioPerfil');
            exit;
        }
        $novaSenha = $_POST['senha'] ?? '';
        if ($novaSenha !== '' && (strlen($novaSenha) < 8 || strlen($novaSenha) > 72)) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Senha inválida',
                'text' => 'A nova senha deve ter entre 8 e 72 caracteres.'
            ];
            header('Location: index.php?action=relatorioPerfil');
            exit;
        }

        $contaAnterior = (new Conta())->buscarPorId($funcional);
        if (!$contaAnterior) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Conta não encontrada',
                'text' => 'Não foi possível localizar a conta deste perfil.'
            ];
            header('Location: index.php?action=login');
            exit;
        }

        $diretorioFotos = __DIR__ . '/../assets/img/perfil/';
        try {
            $nomeFoto = UploadImagem::salvarOpcional('foto', $diretorioFotos);
        } catch (RuntimeException $erro) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Foto inválida',
                'text' => $erro->getMessage()
            ];
            header('Location: index.php?action=relatorioPerfil');
            exit;
        }

        $conexao = Conexao::getConn();
        try {
            $conexao->beginTransaction();
            $modelFunc = new Funcionario();
            if (!$modelFunc->atualizarDadosProprios($funcional, $telefone, $nomeFoto)) {
                throw new RuntimeException('Não foi possível atualizar os dados do perfil.');
            }
            if ($novaSenha !== '') {
                if (!(new Conta())->atualizarSenha($funcional, $novaSenha)) {
                    throw new RuntimeException('Não foi possível atualizar a senha do perfil.');
                }
            }
            $conexao->commit();
        } catch (Throwable $erro) {
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $this->removerFotoTemporaria($diretorioFotos, $nomeFoto);
            error_log($erro->getMessage());
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Erro ao atualizar perfil',
                'text' => 'Não foi possível salvar as alterações. Tente novamente.'
            ];
            header('Location: index.php?action=relatorioPerfil');
            exit;
        }
        if ($nomeFoto !== null) {
            $this->removerFotoAntiga($diretorioFotos, $contaAnterior['foto'] ?? null);
        }

        // O cabeçalho exibirá o sucesso após voltar ao perfil.
        $_SESSION['alerta'] = [
            'icon' => 'success',
            'title' => 'Perfil atualizado!',
            'text' => 'Suas alterações foram salvas.'
        ];
        header('Location: index.php?action=relatorioPerfil');
        exit;
    }

    private function removerFotoTemporaria(string $diretorio, ?string $nomeFoto): void {
        if ($nomeFoto === null) {
            return;
        }

        $caminho = $diretorio . $nomeFoto;
        if (is_file($caminho) && !unlink($caminho)) {
            error_log('Não foi possível remover o upload temporário: ' . $caminho);
        }
    }

    private function removerFotoAntiga(string $diretorio, ?string $nomeFoto): void {
        if ($nomeFoto === null || $nomeFoto === '') {
            return;
        }

        $nomeFoto = basename($nomeFoto);
        if (in_array(strtolower($nomeFoto), ['default.jpg', 'default.png', 'default-avatar.svg'], true)) {
            return;
        }

        $this->removerFotoTemporaria($diretorio, $nomeFoto);
    }
}
?>
