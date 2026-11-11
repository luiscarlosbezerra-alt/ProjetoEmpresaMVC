<?php
// Controller que recebe requisições e chama o model de cargos.
    require_once __DIR__ . '/../model/cargoModel.php';
    class CargoController {
        public function salvar() {
            // Lê o formulário e cria um cargo no banco.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=formCargo');
                exit;
            }

            $nome = trim((string) ($_POST['nome'] ?? ''));
            if ($nome === '' || strlen($nome) > 100) {
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Nome inválido', 'text' => 'Informe um nome de cargo com até 100 caracteres.'];
                header('Location: index.php?action=formCargo');
                exit;
            }

                $salario = $this->normalizarSalario($_POST['salario'] ?? '');
                if ($salario === false) {
                    $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Salário inválido', 'text' => 'Informe um valor válido, como R$ 1.250,00.'];
                    header('Location: index.php?action=formCargo');
                    exit;
                }
                $model = new Cargo();
                try {
                    $salvo = $model->inserir($nome, $salario);
                } catch (PDOException $erro) {
                    if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                        || (int) ($erro->errorInfo[1] ?? 0) !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Cargo já cadastrado',
                        'text' => 'Já existe um cargo com esse nome.'
                    ];
                    header('Location: index.php?action=formCargo');
                    exit;
                }
                if ($salvo) {
                    $_SESSION['alerta'] = [
                        'icon' => 'success',
                        'title' => 'Cargo salvo!',
                        'text' => 'O cargo foi cadastrado com sucesso.'
                    ];
                    header('Location: index.php?action=listaCargo');
                    exit;
                }
        }
        public function listar() {
            // Busca os cargos e envia os dados para a tabela.
            $model = new Cargo();
            $listaCargos = $model->listarTodos();
            include __DIR__."/../view/cargoLista.php";
        }
        public function editar() {
            // Busca um cargo e abre o formulário preenchido.
            $cargo = (new Cargo())->buscarPorId($_GET['id'] ?? null);
            if (!$cargo) { header('Location: index.php?action=listaCargo'); exit; }
            include __DIR__."/../view/cargoEditar.php";
        }
        public function atualizar() {
            // Grava os dados editados e retorna à listagem.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=listaCargo');
                exit;
            }

                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                $nome = trim((string) ($_POST['nome'] ?? ''));
                if ($id === false || $id < 1 || $nome === '' || strlen($nome) > 100) {
                    $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Dados inválidos', 'text' => 'Informe um nome de cargo válido.'];
                    header('Location: index.php?action=listaCargo');
                    exit;
                }
                $salario = $this->normalizarSalario($_POST['salario'] ?? '');
                if ($salario === false) {
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Salário inválido',
                        'text' => 'Informe um salário não negativo com até duas casas decimais e dentro do limite permitido.'
                    ];

                    header('Location: index.php?action=editarCargo&id=' . $id);
                    exit;
                }
                try {
                    $sucesso = (new Cargo())->atualizar($id, $nome, $salario);
                } catch (PDOException $erro) {
                    if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                        || (int) ($erro->errorInfo[1] ?? 0) !== 1062) {
                        throw $erro;
                    }
                    $_SESSION['alerta'] = [
                        'icon' => 'error',
                        'title' => 'Cargo já cadastrado',
                        'text' => 'Outro cargo já utiliza esse nome.'
                    ];
                    header('Location: index.php?action=editarCargo&id=' . $id);
                    exit;
                }
                // Guarda o resultado para o cabeçalho exibir após o redirect.
                $_SESSION['alerta'] = [
                    'icon' => $sucesso ? 'success' : 'error',
                    'title' => $sucesso ? 'Cargo atualizado!' : 'Erro ao atualizar',
                    'text' => $sucesso ? 'As alterações foram salvas.' : 'Não foi possível atualizar o cargo.'
                ];
            header('Location: index.php?action=listaCargo'); exit;
        }
        public function excluir() {
            // Remove o cargo, tratando vínculos existentes.
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?action=listaCargo');
                exit;
            }

            try {
                (new Cargo())->deletar($_POST['id'] ?? null);
                // Informa à lista que a exclusão foi concluída.
                $_SESSION['alerta'] = ['icon' => 'success', 'title' => 'Cargo excluído!', 'text' => 'O registro foi removido.'];
            }
            catch (PDOException $erro) {
                if (($erro->errorInfo[0] ?? (string) $erro->getCode()) !== '23000'
                    || (int) ($erro->errorInfo[1] ?? 0) !== 1451) {
                    throw $erro;
                }
                // A chave estrangeira impede a exclusão de cargos utilizados.
                $_SESSION['alerta'] = ['icon' => 'error', 'title' => 'Não foi possível excluir', 'text' => 'Existem funcionários vinculados a este cargo.'];
            }
            header('Location: index.php?action=listaCargo'); exit;
        }

        private function normalizarSalario($valor) {
            $valor = trim((string) $valor);
            if ($valor === '') {
                return false;
            }

            if (preg_match('/^R\$\s*/u', $valor)) {
                $valor = preg_replace('/^R\$\s*/u', '', $valor);
            }

            if (preg_match('/^(?:\d{1,8}|\d{1,3}(?:\.\d{3})+),\d{1,2}$/', $valor)) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            } elseif (preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $valor)) {
                // Aceita também decimal com ponto usado por dados já existentes.
            } elseif (preg_match('/^\d{1,3}(?:\.\d{3})+$/', $valor)) {
                $valor = str_replace('.', '', $valor);
            } else {
                return false;
            }

            return $valor;
        }
    }
?>
