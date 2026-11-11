<?php 
    // Lista departamentos com as ações de edição e exclusão.
    include __DIR__."/include/cabecalho.php";
?>

    <h2>Relatório de Departamentos</h2>
    <p class="subtitulo">Dados processados pelo Controller e exibidos pela View</p>
    <!-- Os registros são fornecidos pelo DepController. -->
    <table class="tabela-dados">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($listaDepartamentos) && count($listaDepartamentos) > 0): ?>
                <?php foreach($listaDepartamentos as $d): ?>
                <tr>
                    <td>#<?=$d['codDepartamento']?></td>
                    <td><?= htmlspecialchars($d['nomeDepartamento']) ?></td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarDep&id=<?=$d['codDepartamento']?>">Editar</a>
                        <form class="form-exclusao" action="index.php?action=excluirDep" method="post" onsubmit="return confirmarExclusao(event, this, 'departamento');">
                            <input type="hidden" name="id" value="<?= (int) $d['codDepartamento'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(TokenCsrf::obter(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn-acao btn-excluir" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
        <?php else: ?>
                <tr>
                    <td colspan="3" class="msg-vazia">
                        Nenhum registro para exibir.
                    </td>
                </tr>
        <?php endif; ?>
        </tbody>
    </table>

<?php 
    include __DIR__."/include/rodape.php";
?>
