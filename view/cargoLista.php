<?php 
    // Lista cargos e oferece as ações administrativas do CRUD.
    include __DIR__."/include/cabecalho.php";
?>

    <h2>Relatório de Cargos</h2>
    <p class="subtitulo">Dados processados pelo Controller e exibidos pela View</p>

    <!-- Os dados desta tabela vêm do CargoController. -->
    <table class="tabela-dados">
        <thead>
            <tr>
                <th>Código</th>
                <th>Descrição</th>
                <th class="col-valor">Salário</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php if (isset($listaCargos) && count($listaCargos) > 0): ?>
                <?php foreach($listaCargos as $c): ?>
                <tr>
                    <td>#<?=$c['codCargo']?></td>
                    <td><?= htmlspecialchars($c['nomeCargo']) ?></td>
                    <td class="col-valor">
                        R$ <?= number_format($c['salario'], 2, ',', '.') ?>
                    </td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarCargo&id=<?=$c['codCargo']?>">Editar</a>
                        <form class="form-exclusao" action="index.php?action=excluirCargo" method="post" onsubmit="return confirmarExclusao(event, this, 'cargo');">
                            <input type="hidden" name="id" value="<?= (int) $c['codCargo'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(TokenCsrf::obter(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn-acao btn-excluir" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
        <?php else: ?>
                <tr>
                    <td colspan="4" class="msg-vazia">
                        Nenhum registro para exibir.
                    </td>
                </tr>
        <?php endif; ?>

        </tbody>
    </table>

<?php 
    include __DIR__."/include/rodape.php";
?>
