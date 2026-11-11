<?php
// Model que concentra consultas e alterações de funcionários.
    require_once __DIR__ . '/../config/conexao.php';
    class Funcionario {
        // Insere o funcionário e seus vínculos com cargo e departamento.
        public function inserir($nome, $cpf, $telefone, array $endereco, $codDep, $codCargo) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                $idEndereco = $this->salvarEndereco($conexao, $endereco);
                $sql = "INSERT INTO dboFuncionario
                        (nome, cpf, telefone, numero, complemento, idEndereco, codDepartamento, codCargo)
                        VALUES
                        (:nome, :cpf, :telefone, :numero, :complemento, :idEndereco, :codDepartamento, :codCargo)";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':telefone' => $telefone,
                    ':numero' => $endereco['numero'],
                    ':complemento' => $endereco['complemento'] !== '' ? $endereco['complemento'] : null,
                    ':idEndereco' => $idEndereco,
                    ':codDepartamento' => $codDep,
                    ':codCargo' => $codCargo,
                ]);

                $conexao->commit();
                return true;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }
        // Lista funcionários trazendo também os nomes dos relacionamentos.
        public function listarTodos(){
            $sql = "SELECT f.*, d.nomeDepartamento, c.nomeCargo,
                        e.cep, e.logradouro, e.bairro, e.cidade, e.uf
                FROM dboFuncionario f
                INNER JOIN dboDepartamento d ON f.codDepartamento = d.codDepartamento
                INNER JOIN dboCargo c ON f.codCargo = c.codCargo
                LEFT JOIN dboEndereco e ON f.idEndereco = e.idEndereco
                ORDER BY f.funcional ASC";
            $stmt= Conexao::getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // Atualiza os dados administrativos do funcionário.
        public function atualizar($id, $nome, $cpf, $telefone, array $endereco, $codDep, $codCargo) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                $idEndereco = $this->salvarEndereco($conexao, $endereco);
                $sql = "UPDATE dboFuncionario SET nome = :nome, cpf = :cpf, telefone = :telefone,
                        numero = :numero, complemento = :complemento, idEndereco = :idEndereco,
                        codDepartamento = :codDepartamento, codCargo = :codCargo
                        WHERE funcional = :id";
                $stmt = $conexao->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':cpf' => $cpf,
                    ':telefone' => $telefone,
                    ':numero' => $endereco['numero'],
                    ':complemento' => $endereco['complemento'] !== '' ? $endereco['complemento'] : null,
                    ':idEndereco' => $idEndereco,
                    ':codDepartamento' => $codDep,
                    ':codCargo' => $codCargo,
                    ':id' => $id,
                ]);

                $conexao->commit();
                return true;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }
        // Exclui o funcionário quando não existe conta vinculada.
        public function deletar($id) {
            $stmt = Conexao::getConn()->prepare("delete from dboFuncionario where funcional = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        // Confere funcional e CPF durante a criação de uma conta.
        public function verificarDadosIniciais($funcional, $cpf) {
            $sql = "SELECT funcional, nome FROM dboFuncionario WHERE funcional = :funcional AND cpf = :cpf";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        // Busca os dados do perfil e seus relacionamentos.
        public function buscarPorId($funcional) {
                $sql = "SELECT f.*, c.nomeCargo, d.nomeDepartamento AS nomeDepartamento, co.foto,
                    e.cep, e.logradouro, e.bairro, e.cidade, e.uf
                    FROM dboFuncionario f
                    INNER JOIN dboCargo c ON f.codCargo = c.codCargo
                    INNER JOIN dboDepartamento d ON f.codDepartamento = d.codDepartamento
                    LEFT JOIN dboConta co ON f.funcional = co.funcional
                    LEFT JOIN dboEndereco e ON f.idEndereco = e.idEndereco
                    WHERE f.funcional = :funcional";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        private function salvarEndereco(PDO $conexao, array $endereco) {
            $sql = "INSERT INTO dboEndereco
                    (cep, logradouro, bairro, cidade, uf)
                    VALUES (:cep, :logradouro, :bairro, :cidade, :uf)
                    ON DUPLICATE KEY UPDATE
                        idEndereco = LAST_INSERT_ID(idEndereco),
                        logradouro = VALUES(logradouro),
                        bairro = VALUES(bairro),
                        cidade = VALUES(cidade),
                        uf = VALUES(uf)";
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                ':cep' => $endereco['cep'],
                ':logradouro' => $endereco['logradouro'],
                ':bairro' => $endereco['bairro'],
                ':cidade' => $endereco['cidade'],
                ':uf' => $endereco['uf'],
            ]);
            $idEndereco = (int) $conexao->lastInsertId();
            if ($idEndereco < 1) {
                throw new RuntimeException('Não foi possível identificar o endereço salvo.');
            }
            return $idEndereco;
        }

        // Permite alterar apenas telefone e foto no próprio perfil.
        public function atualizarDadosProprios($funcional, $telefone, $foto = null) {
            if ($foto !== null) {
                $sql = "UPDATE dboFuncionario f
                        INNER JOIN dboConta c ON c.funcional = f.funcional
                        SET f.telefone = :telefone, c.foto = :foto
                        WHERE f.funcional = :funcional";
            } else {
                $sql = "UPDATE dboFuncionario
                        SET telefone = :telefone
                        WHERE funcional = :funcional";
            }

            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);

            if ($foto !== null) {
                $stmt->bindParam(':foto', $foto, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

    }
?>
