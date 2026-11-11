<?php
// Model das contas de acesso e credenciais dos funcionários.
    require_once __DIR__ . '/../config/conexao.php';

    class Conta {
        // Cria uma conta armazenando a senha protegida por hash.
        public function inserir($funcional, $login, $senha, $foto, $nivelAcesso) {
            if (!in_array((int) $nivelAcesso, [0, 1], true)) {
                throw new InvalidArgumentException('Nível de acesso inválido.');
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO dboConta (funcional, login, senha, foto, nivelAcesso)
                    VALUES (:f, :l, :s, :i, :nivel)";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':f', $funcional);
            $stmt->bindParam(':l', $login);
            $stmt->bindParam(':s', $senhaHash);
            $stmt->bindParam(':i', $foto);
            $stmt->bindValue(':nivel', (int) $nivelAcesso, PDO::PARAM_INT);
            return $stmt->execute();
        }

        public function listarFuncionariosSemConta() {
            $sql = "SELECT f.funcional, f.nome, f.cpf
                    FROM dboFuncionario f
                    LEFT JOIN dboConta c ON c.funcional = f.funcional
                    WHERE c.funcional IS NULL
                    ORDER BY f.nome, f.funcional";
            return Conexao::getConn()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }

        public function buscarFuncionarioSemConta($funcional) {
            $stmt = Conexao::getConn()->prepare(
                "SELECT f.funcional, f.nome
                 FROM dboFuncionario f
                 LEFT JOIN dboConta c ON c.funcional = f.funcional
                 WHERE f.funcional = :funcional AND c.funcional IS NULL"
            );
            $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Lista contas junto com os nomes dos funcionários.
        public function listarConta() {
            $sql = "SELECT c.funcional, f.nome, c.login, c.foto, c.nivelAcesso
                    FROM dboConta AS c
                    INNER JOIN dboFuncionario AS f
                    ON c.funcional = f.funcional
                    ORDER BY c.funcional ASC";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Confere se outro administrador continuará ativo após uma autoexclusão.
        public function existeAdministradorDiferente($funcional) {
            $stmt = Conexao::getConn()->prepare(
                "SELECT COUNT(*) FROM dboConta WHERE nivelAcesso = 0 AND funcional <> :funcional"
            );
            $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn() > 0;
        }

        // Busca uma conta pelo funcionário dono dela.
        public function buscarPorId($funcional) {
            $sql = "SELECT c.*, f.nome FROM dboConta c INNER JOIN dboFuncionario f ON c.funcional = f.funcional WHERE c.funcional = :funcional";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function buscarNivelAcesso($funcional) {
            $stmt = Conexao::getConn()->prepare(
                "SELECT nivelAcesso FROM dboConta WHERE funcional = :funcional"
            );
            $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
            $stmt->execute();
            $nivel = $stmt->fetchColumn();
            return $nivel === false ? null : (int) $nivel;
        }

        // Atualiza login, nível e opcionalmente senha e foto.
        public function atualizar($funcional, $login, $nivel, $senha = null, $foto = null) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                if ($nivel === 1) {
                    $administradores = $this->bloquearAdministradores($conexao);
                    $ehAdministrador = in_array((int) $funcional, $administradores, true);
                    if ($ehAdministrador && count($administradores) <= 1) {
                        throw new DomainException('O sistema precisa manter ao menos um administrador.');
                    }
                }

                $campos = 'login = :login, nivelAcesso = :nivel';
                if (!empty($senha)) { $campos .= ', senha = :senha'; }
                if ($foto !== null) { $campos .= ', foto = :foto'; }
                $stmt = $conexao->prepare("UPDATE dboConta SET $campos WHERE funcional = :funcional");
                $stmt->bindParam(':login', $login);
                $stmt->bindValue(':nivel', $nivel, PDO::PARAM_INT);
                if (!empty($senha)) {
                    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt->bindParam(':senha', $senhaHash);
                }
                if ($foto !== null) { $stmt->bindParam(':foto', $foto); }
                $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
                $sucesso = $stmt->execute();
                $conexao->commit();
                return $sucesso;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }
        // Remove a conta de acesso sem remover o funcionário.
        public function deletar($funcional) {
            $conexao = Conexao::getConn();
            $conexao->beginTransaction();

            try {
                $administradores = $this->bloquearAdministradores($conexao);
                if (in_array((int) $funcional, $administradores, true) && count($administradores) <= 1) {
                    throw new DomainException('O sistema precisa manter ao menos um administrador.');
                }

                $stmt = $conexao->prepare("delete from dboConta where funcional = :funcional");
                $stmt->bindValue(':funcional', $funcional, PDO::PARAM_INT);
                $sucesso = $stmt->execute();
                $conexao->commit();
                return $sucesso;
            } catch (Throwable $erro) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                throw $erro;
            }
        }

        // Consulta uma conta pelo login usado na autenticação.
        public function buscarPorLogin($login) {
            $sql = "SELECT c.funcional, f.nome, c.login, c.senha, c.nivelAcesso
                    FROM dboConta AS c
                    INNER JOIN dboFuncionario AS f
                    ON c.funcional = f.funcional
                    WHERE c.login = :login";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Atualiza uma senha que já foi convertida para hash.
        public function atualizarSenhaHash($funcional, $senhaHash) {
            $sql = "UPDATE dboConta SET senha = :senha WHERE funcional = :funcional";
            $stmt = Conexao::getConn()->prepare($sql);
            $stmt->bindParam(':senha', $senhaHash);
            $stmt->bindParam(':funcional', $funcional, PDO::PARAM_INT);
            return $stmt->execute();
        }

        // Gera o hash antes de salvar a nova senha.
        public function atualizarSenha($funcional, $senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            return $this->atualizarSenhaHash($funcional, $senhaHash);
        }

        private function bloquearAdministradores(PDO $conexao): array {
            $stmt = $conexao->query(
                'SELECT funcional FROM dboConta WHERE nivelAcesso = 0 FOR UPDATE'
            );
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }
    }
?>
