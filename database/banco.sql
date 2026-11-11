-- DROP DATABASE IF EXISTS dboempresaMvc;
CREATE DATABASE IF NOT EXISTS dboEmpresaMvc
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE dboEmpresaMvc;

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS dboCargo (
    codCargo INT AUTO_INCREMENT PRIMARY KEY,
    nomeCargo VARCHAR(100) NOT NULL UNIQUE,
    salario DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dboDepartamento (
    codDepartamento INT AUTO_INCREMENT PRIMARY KEY,
    nomeDepartamento VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dboEndereco (
    idEndereco INT AUTO_INCREMENT PRIMARY KEY,
    cep CHAR(8) NOT NULL UNIQUE,
    logradouro VARCHAR(150) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    uf CHAR(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dboFuncionario (
    funcional INT AUTO_INCREMENT PRIMARY KEY,
    cpf CHAR(11) NOT NULL UNIQUE,
    nome VARCHAR(40) NOT NULL,
    telefone VARCHAR(11) NULL,
    numero VARCHAR(20) NOT NULL,
    complemento VARCHAR(100) NULL,
    idEndereco INT NOT NULL,
    codDepartamento INT NOT NULL,
    codCargo INT NOT NULL,

    CONSTRAINT fkFuncionarioEndereco
        FOREIGN KEY (idEndereco)
        REFERENCES dboEndereco(idEndereco),

    CONSTRAINT fkFuncionarioDepartamento
        FOREIGN KEY (codDepartamento)
        REFERENCES dboDepartamento(codDepartamento),

    CONSTRAINT fkFuncionarioCargo
        FOREIGN KEY (codCargo)
        REFERENCES dboCargo(codCargo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dboConta (
    funcional INT PRIMARY KEY,
    login VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto VARCHAR(255) NULL,
    nivelAcesso TINYINT NOT NULL DEFAULT 1,

    CONSTRAINT fkContaFuncionario
        FOREIGN KEY (funcional)
        REFERENCES dboFuncionario(funcional)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dboSolicitacaoAusencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcional INT NOT NULL,
    solicitanteNome VARCHAR(40) NOT NULL,
    tipo ENUM('ferias', 'ausencia') NOT NULL,
    dataInicio DATE NOT NULL,
    dataFim DATE NOT NULL,
    motivo VARCHAR(500) NOT NULL,
    status ENUM('pendente', 'aprovada', 'recusada', 'cancelada')
        NOT NULL DEFAULT 'pendente',
    respostaAdmin VARCHAR(500) NULL,
    solicitadoEm TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    avaliadoPor INT NULL,
    avaliadorNome VARCHAR(40) NULL,
    avaliadoEm TIMESTAMP NULL DEFAULT NULL,

    INDEX idx_solicitacao_funcionario (funcional, solicitadoEm),
    INDEX idx_solicitacao_status_data (status, dataInicio, dataFim),

    CONSTRAINT fkSolicitacaoFuncionario
        FOREIGN KEY (funcional)
        REFERENCES dboFuncionario(funcional)
        ON DELETE RESTRICT,

    CONSTRAINT fkSolicitacaoAvaliador
        FOREIGN KEY (avaliadoPor)
        REFERENCES dboFuncionario(funcional)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO dboDepartamento (nomeDepartamento) VALUES
('Administrativo'),
('Contabilidade'),
('TI'),
('RH'),
('Marketing'),
('Atendimento');

INSERT IGNORE INTO dboCargo (nomeCargo, salario) VALUES
('Gerente', 8500.00),
('Coordenador', 6500.00),
('Analista', 4200.00),
('Assistente', 2800.00),
('Estagiário', 1500.00);

INSERT IGNORE INTO dboEndereco
(cep, logradouro, bairro, cidade, uf)
VALUES
('01001000', 'Praça da Sé', 'Sé', 'São Paulo', 'SP');

INSERT IGNORE INTO dboFuncionario
(cpf, nome, telefone, numero, complemento, idEndereco, codDepartamento, codCargo)
SELECT
'52998224725', 'Luis Admin', '11987654321', '123', NULL,
e.idEndereco, d.codDepartamento, c.codCargo
FROM dboEndereco e
JOIN dboDepartamento d ON d.nomeDepartamento = 'Administrativo'
JOIN dboCargo c ON c.nomeCargo = 'Gerente'
WHERE e.cep = '01001000';

INSERT IGNORE INTO dboFuncionario
(cpf, nome, telefone, numero, complemento, idEndereco, codDepartamento, codCargo)
SELECT
'11144477735', 'Joao Vitor', '11987654322', '456', NULL,
e.idEndereco, d.codDepartamento, c.codCargo
FROM dboEndereco e
JOIN dboDepartamento d ON d.nomeDepartamento = 'TI'
JOIN dboCargo c ON c.nomeCargo = 'Analista'
WHERE e.cep = '01001000';

INSERT IGNORE INTO dboConta
(funcional, login, senha, foto, nivelAcesso)
SELECT
f.funcional,
'luis.admin',
'$2y$10$pA5gRdLAEpo404S2w0mifODCNTwNPGkGTgHKyLMjc01aPxBEavPsm',
'default-avatar.svg',
0
FROM dboFuncionario f
WHERE f.cpf = '52998224725';

INSERT IGNORE INTO dboConta
(funcional, login, senha, foto, nivelAcesso)
SELECT
f.funcional,
'joao.vitor',
'$2y$10$pA5gRdLAEpo404S2w0mifODCNTwNPGkGTgHKyLMjc01aPxBEavPsm',
'default-avatar.svg',
1
FROM dboFuncionario f
WHERE f.cpf = '11144477735';