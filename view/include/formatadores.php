<?php

function formatarCpfExibicao(?string $cpf): string
{
    $digitos = preg_replace('/\D/', '', $cpf ?? '');
    if (strlen($digitos) !== 11) {
        return $cpf ?? '';
    }

    return substr($digitos, 0, 3) . '.'
        . substr($digitos, 3, 3) . '.'
        . substr($digitos, 6, 3) . '-'
        . substr($digitos, 9, 2);
}

function formatarTelefoneExibicao(?string $telefone): string
{
    $digitos = preg_replace('/\D/', '', $telefone ?? '');
    if (!in_array(strlen($digitos), [10, 11], true)) {
        return $telefone ?? '';
    }

    $ddd = substr($digitos, 0, 2);
    $numero = substr($digitos, 2);
    $tamanhoPrefixo = strlen($digitos) === 11 ? 5 : 4;

    return '(' . $ddd . ') '
        . substr($numero, 0, $tamanhoPrefixo) . '-'
        . substr($numero, $tamanhoPrefixo);
}
