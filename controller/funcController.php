<?php
// Controller do cadastro, consulta e manutenção de funcionários.
    require_once __DIR__ . '/../model/funcionarioModel.php';
    require_once __DIR__ . '/../model/cargoModel.php';
    require_once __DIR__ . '/../model/departamentoModel.php';
    class FuncController {
        public function salvar() {
            // Recebe dados do formulário e cria o funcionário.
            if ($_SERVER['REQUEST_METHOD'] == "POST") {
                $nome = trim((string) ($_POST['nome'] ?? ''));
                $cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
                $telefone = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
                $endereco = $this->validarEndereco($_POST['endereco'] ?? []);
                $codDep = filter_var($_POST['codDepartamento'] ?? null, FILTER_VALIDATE_INT);
                $codCargo = filter_var($_POST['codCargo'] ?? null, FILTER_VALIDATE_INT);
                if ($nome === '' || strlen($nome) > 40
                    || !$this->cpfValido($cpf)
                    || ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true))
                    || $endereco === false
                    || $codDep === false || $codDep < 1
                    || $codCargo === false || $codCargo < 1
                    || !(new Departamento())->buscarPorId($codDep)
                    || !(new Cargo())->buscarPorId($codCargo)) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Confira os dados',
                        'text' => 'Preencha os campos obrigatórios e informe CPF, telefone, endereço, departamento e cargo válidos.'
                    ];
                    header('Location: index.php?action=formFunc');
                    exit;
                }

                $model = new Funcionario();
                try {
                    $salvo = $model->inserir(
                        $nome,
                        $cpf,
                        $telefone,
                        $endereco,
                        $codDep,
                        $codCargo
                    );
                } catch (PDOException $erro) {
                    $sqlState = $erro->errorInfo[0] ?? (string) $erro->getCode();
                    $codigoMysql = (int) ($erro->errorInfo[1] ?? 0);
                    if ($sqlState === '23000' && $codigoMysql === 1452) {
                        $_SESSION['alerta'] = [
                            'icon' => 'error',
                            'title' => 'Referência inválida',
                            'text' => 'O departamento ou cargo selecionado não está mais disponível.'
                        ];
                        header('Location: index.php?action=formFunc');
                        exit;
                    }
                    if ($sqlState !== '23000' || $codigoMysql !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'CPF já cadastrado',
                        'text' => 'Já existe um funcionário cadastrado com este CPF.'
                    ];
                    header('Location: index.php?action=formFunc');
                    exit;
                }

                if ($salvo) {
                    $_SESSION['alerta'] = [
                        'icon' => 'success',
                        'title' => 'Funcionário cadastrado!',
                        'text' => 'O funcionário foi salvo com sucesso.'
                    ];
                    header('Location: index.php?action=listaFunc');
                    exit;
                } else {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Erro ao salvar',
                        'text' => 'Não foi possível cadastrar o funcionário.'
                    ];
                    header('Location: index.php?action=formFunc');
                    exit;
                }
            }
        }
        public function listarTodos() {
            // Busca funcionários com cargo e departamento relacionados.
            $model = new Funcionario();
            $listaFuncionarios = $model->listarTodos();
            include __DIR__ . "/../view/funcLista.php";
        }
        public function editar() {
            // Prepara o funcionário e as opções dos selects para edição.
            $funcionario = (new Funcionario())->buscarPorId($_GET['id'] ?? null);
            if (!$funcionario) { header('Location: index.php?action=listaFunc'); exit; }
            $listaCargo = (new Cargo())->listarTodos();
            $listaDep = (new Departamento())->listarTodos();
            include __DIR__ . "/../view/funcEditar.php";
        }
        public function atualizar() {
            // Salva os dados administrativos alterados.
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $id = filter_var($_POST['funcional'] ?? null, FILTER_VALIDATE_INT);
                $nome = trim((string) ($_POST['nome'] ?? ''));
                $cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
                $telefone = preg_replace('/\D/', '', (string) ($_POST['telefone'] ?? ''));
                $endereco = $this->validarEndereco($_POST['endereco'] ?? []);
                $codDep = filter_var($_POST['codDepartamento'] ?? null, FILTER_VALIDATE_INT);
                $codCargo = filter_var($_POST['codCargo'] ?? null, FILTER_VALIDATE_INT);
                if ($id === false || $id < 1
                    || $nome === '' || strlen($nome) > 40
                    || !$this->cpfValido($cpf)
                    || ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true))
                    || $endereco === false
                    || $codDep === false || $codDep < 1
                    || $codCargo === false || $codCargo < 1
                    || !(new Departamento())->buscarPorId($codDep)
                    || !(new Cargo())->buscarPorId($codCargo)) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Confira os dados',
                        'text' => 'Informe CPF, telefone e endereço nos formatos válidos.'
                    ];
                    header('Location: index.php?action=editarFunc&id=' . ($id !== false && $id > 0 ? $id : ''));
                    exit;
                }

                try {
                    $sucesso = (new Funcionario())->atualizar(
                        $id,
                        $nome,
                        $cpf,
                        $telefone,
                        $endereco,
                        $codDep,
                        $codCargo
                    );
                } catch (PDOException $erro) {
                    $sqlState = $erro->errorInfo[0] ?? (string) $erro->getCode();
                    $codigoMysql = (int) ($erro->errorInfo[1] ?? 0);
                    if ($sqlState === '23000' && $codigoMysql === 1452) {
                        $_SESSION['alerta'] = [
                            'icon' => 'error',
                            'title' => 'Referência inválida',
                            'text' => 'O departamento ou cargo selecionado não está mais disponível.'
                        ];
                        header('Location: index.php?action=editarFunc&id=' . $id);
                        exit;
                    }
                    if ($sqlState !== '23000' || $codigoMysql !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'CPF já cadastrado',
                        'text' => 'Outro funcionário já utiliza este CPF.'
                    ];
                    header('Location: index.php?action=editarFunc&id=' . $id);
                    exit;
                }
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Funcionário atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o funcionário.'
                ];
            }
            header('Location: index.php?action=listaFunc'); exit;
        }

        private function validarEndereco($dados) {
            if (!is_array($dados)) {
                return false;
            }

            $endereco = [
                'cep' => preg_replace('/\D/', '', (string) ($dados['cep'] ?? '')),
                'logradouro' => trim((string) ($dados['logradouro'] ?? '')),
                'numero' => trim((string) ($dados['numero'] ?? '')),
                'complemento' => trim((string) ($dados['complemento'] ?? '')),
                'bairro' => trim((string) ($dados['bairro'] ?? '')),
                'cidade' => trim((string) ($dados['cidade'] ?? '')),
                'uf' => strtoupper(trim((string) ($dados['uf'] ?? ''))),
            ];

            if (strlen($endereco['cep']) !== 8
                || $endereco['logradouro'] === '' || strlen($endereco['logradouro']) > 150
                || $endereco['numero'] === '' || strlen($endereco['numero']) > 20
                || strlen($endereco['complemento']) > 100
                || $endereco['bairro'] === '' || strlen($endereco['bairro']) > 100
                || $endereco['cidade'] === '' || strlen($endereco['cidade']) > 100
                || !preg_match('/^[A-Z]{2}$/', $endereco['uf'])) {
                return false;
            }

            return $endereco;
        }

        private function cpfValido(string $cpf): bool {
            return preg_match('/^\d{11}$/', $cpf) === 1;
        }
        public function excluir() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=listaFunc');
                exit;
            }

            // Exclui apenas funcionários sem conta vinculada.
            try {
                (new Funcionario())->deletar($_POST['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Funcionário excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                    || (int) ($erro->errorInfo[1] ?? 0) !== 1451) {
                    throw $erro;
                }
                $_SESSION['alerta'] = [
                    'icon' => 'error',
                    'title' => 'Não foi possível excluir',
                    'text' => 'Remova a conta de acesso e verifique solicitações associadas antes de excluir o funcionário.'
                ];
            }
            header('Location: index.php?action=listaFunc'); exit;
        }
    }
?>
