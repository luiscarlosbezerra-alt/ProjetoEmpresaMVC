<?php
// Controller responsável pelo fluxo de departamentos.
    require_once __DIR__ . '/../model/departamentoModel.php';
    class DepController {
        public function salvar() {
            // Valida o método e envia o novo nome ao model.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=formDep');
                exit;
            }

                $nome = trim((string) ($_POST['nome'] ?? ''));
                if ($nome === '' || strlen($nome) > 50) {
                    $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Nome inválido', 'text' => 'Informe um nome de departamento com até 50 caracteres.'];
                    header('Location: index.php?action=formDep');
                    exit;
                }
                $model = new Departamento();
                try {
                    $salvo = $model->inserir($nome);
                } catch (PDOException $erro) {
                    if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                        || (int) ($erro->errorInfo[1] ?? 0) !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Departamento já cadastrado',
                        'text' => 'Já existe um departamento com esse nome.'
                    ];
                    header('Location: index.php?action=formDep');
                    exit;
                }
                if ($salvo) {
                    $_SESSION['alerta'] = [
                        'icon' => 'success',
                        'title' => 'Departamento salvo!',
                        'text' => 'O departamento foi cadastrado com sucesso.'
                    ];
                    header('Location: index.php?action=listaDep');
                    exit;
                }
        }
        public function listar() {
            // Busca departamentos e carrega a view de relatório.
            $model = new Departamento();
            $listaDepartamentos = $model->listarTodos();
            include __DIR__ . "/../view/depLista.php";
        }
        public function editar() {
            // Carrega um departamento no formulário de edição.
            $departamento = (new Departamento())->buscarPorId($_GET['id'] ?? null);
            if (!$departamento) { header('Location: index.php?action=listaDep'); exit; }
            include __DIR__ . "/../view/depEditar.php";
        }
        public function atualizar() {
            // Persiste o novo nome do departamento.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=listaDep');
                exit;
            }

                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                $nome = trim((string) ($_POST['nome'] ?? ''));
                if ($id === false || $id < 1 || $nome === '' || strlen($nome) > 50) {
                    $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Dados inválidos', 'text' => 'Informe um nome de departamento válido.'];
                    header('Location: index.php?action=listaDep');
                    exit;
                }
                try {
                    $sucesso = (new Departamento())->atualizar($id, $nome);
                } catch (PDOException $erro) {
                    if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                        || (int) ($erro->errorInfo[1] ?? 0) !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Departamento já cadastrado',
                        'text' => 'Outro departamento já utiliza esse nome.'
                    ];
                    header('Location: index.php?action=editarDep&id=' . $id);
                    exit;
                }
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Departamento atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o departamento.'
                ];
            header('Location: index.php?action=listaDep'); exit;
        }
        public function excluir() {
            // Remove o departamento quando não há dependências.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=listaDep');
                exit;
            }

            try {
                (new Departamento())->deletar($_POST['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Departamento excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                    || (int) ($erro->errorInfo[1] ?? 0) !== 1451) {
                    throw $erro;
                }
                // A chave estrangeira impede a exclusão de departamentos utilizados.
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Não foi possível excluir', 'text' => 'Existem funcionários vinculados a este departamento.'];
            }
            header('Location: index.php?action=listaDep'); exit;
        }
    }
?>
