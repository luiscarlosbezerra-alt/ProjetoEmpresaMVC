<?php
// Formulário de edição preenchido pelo CargoController.
include __DIR__ . '/include/cabecalho.php'; ?>

<h2>Editar Cargo</h2>
<!-- O código do cargo é mantido em campo oculto para identificar o registro. -->
<form action="index.php?action=atualizarCargo" method="POST" class="form-grupo">
    <input type="hidden" name="id" value="<?= $cargo['codCargo'] ?>">
    <label class="form-label">Nome do Cargo:</label><br>
    <input type="text" name="nome" class="form-input" required maxlength="100" value="<?= htmlspecialchars($cargo['nomeCargo']) ?>"><br>
    <label class="form-label">Salário Base (R$):</label><br>
    <input type="text" inputmode="decimal" name="salario" class="form-input" required maxlength="14" placeholder="R$ 0,00" data-mask="moeda" value="<?= htmlspecialchars($cargo['salario']) ?>"><br>
    <button type="submit" class="btn-save">Salvar Alterações</button>
</form>

<?php include __DIR__ . '/include/rodape.php'; ?>
