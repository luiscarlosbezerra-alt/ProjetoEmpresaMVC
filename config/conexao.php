<?php

// Centraliza a criação da conexão PDO usada pelos models.
class Conexao
{
    private static ?PDO $conexao = null;

    // Abre uma conexão MySQL com tratamento de exceções.
    public static function getConn()
    {
        if (self::$conexao instanceof PDO) {
            return self::$conexao;
        }

        $host = "127.0.0.1";
        $port = "3307";
        $dbname = "dboEmpresaMvc";
        $user = "root";
        $pass = "";

        try {
            self::$conexao = new PDO(
                "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass
            );

            self::$conexao->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return self::$conexao;
        } catch (PDOException $e) {
            throw new RuntimeException('Não foi possível conectar ao banco de dados.', 0, $e);
        }
    }
}
