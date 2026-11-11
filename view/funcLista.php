<?php
    require_once __DIR__ . '/include/formatadores.php';
    // Relatório de funcionários com cargo, departamento e ações do CRUD.
    include __DIR__."/include/cabecalho.php";
?>

    <h2>Relatório de Funcionários</h2>
    <p class="subtitulo">Dados processados pelo Controller e exibidos pela View</p>
    <!-- Os nomes dos relacionamentos já vêm preparados pelo model. -->
    <table class="tabela-dados">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Telefone</th>
                <th>Endereço</th>
                <th>Departamento</th>
                <th>Cargo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($listaFuncionarios) && count($listaFuncionarios) > 0): ?>
                <?php foreach($listaFuncionarios as $f): ?>
                <?php
                    $cpfFormatado = formatarCpfExibicao($f['cpf']);
                    $telefoneFormatado = formatarTelefoneExibicao($f['telefone'] ?? '');
                    $partesEndereco = array_filter([
                        $f['logradouro'] ?? null, $f['numero'] ?? null, $f['complemento'] ?? null,
                        $f['bairro'] ?? null, $f['cidade'] ?? null, $f['uf'] ?? null,
                        !empty($f['cep']) ? 'CEP ' . $f['cep'] : null,
                    ], static function ($parte) { return $parte !== null && $parte !== ''; });
                    if (!$partesEndereco && !empty($f['endereco'])) {
                        $partesEndereco[] = $f['endereco'];
                    }
                ?>
                <tr>
                    <td>#<?= $f['funcional'] ?></td>
                    <td><?= htmlspecialchars($f['nome']) ?></td>
                    <td><?= htmlspecialchars($cpfFormatado) ?></td>
                    <td><?= htmlspecialchars($telefoneFormatado ?? '') ?></td>
                    <td><?= htmlspecialchars(implode(', ', $partesEndereco)) ?></td>
                    <td><?= $f['nomeDepartamento'] ?? '---' ?></td>
                    <td><?= $f['nomeCargo'] ?? '---' ?></td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarFunc&id=<?=$f['funcional']?>">Editar</a>
                        <form class="form-exclusao" action="index.php?action=excluirFunc" method="post" onsubmit="return confirmarExclusao(event, this, 'funcionário');">
                            <input type="hidden" name="id" value="<?= (int) $f['funcional'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(TokenCsrf::obter(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn-acao btn-excluir" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="msg-vazia">
                        Nenhum funcionário cadastrado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php 
    include __DIR__."/include/rodape.php";
?>
