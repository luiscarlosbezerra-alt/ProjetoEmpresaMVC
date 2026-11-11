<?php

class UploadImagem
{
    private const TAMANHO_MAXIMO = 5242880;

    public static function salvarOpcional(string $campo, string $diretorio): ?string
    {
        if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $arquivo = $_FILES[$campo];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Não foi possível receber a imagem enviada.');
        }

        $tamanho = filesize($arquivo['tmp_name']);
        if ($tamanho === false || $tamanho < 1 || $tamanho > self::TAMANHO_MAXIMO) {
            throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
        }

        if (!is_uploaded_file($arquivo['tmp_name'])) {
            throw new RuntimeException('O arquivo enviado não é um upload válido.');
        }

        $imagem = @getimagesize($arquivo['tmp_name']);
        $tiposPermitidos = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $mime = $imagem['mime'] ?? '';

        if (!isset($tiposPermitidos[$mime]) || !in_array($extensao, $tiposPermitidos[$mime], true)) {
            throw new RuntimeException('Envie uma imagem JPG, JPEG, PNG ou WebP válida.');
        }

        if (!is_dir($diretorio) && !@mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
        }

        $nome = bin2hex(random_bytes(16)) . '.' . $extensao;
        if (!move_uploaded_file($arquivo['tmp_name'], rtrim($diretorio, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nome)) {
            throw new RuntimeException('Não foi possível salvar a imagem enviada.');
        }

        return $nome;
    }
}
