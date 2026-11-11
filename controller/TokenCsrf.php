<?php

class TokenCsrf
{
    public static function obter(): string
    {
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function exigirPost(string $action): void
    {
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'
            || !is_string($token)
            || !hash_equals(self::obter(), $token)) {
            $_SESSION['alerta'] = [
                'icon' => 'error',
                'title' => 'Ação não autorizada',
                'text' => 'Atualize a página e tente novamente.'
            ];
            header('Location: index.php?action=' . rawurlencode($action));
            exit;
        }
    }
}
