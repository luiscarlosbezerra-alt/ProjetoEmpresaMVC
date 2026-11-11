<?php
// Formulário de edição preenchido pelo DepController.
include __DIR__ . '/include/cabecalho.php'; ?>

<h2>Editar Departamento</h2>
<!-- O código oculto indica qual departamento será atualizado. -->
<form action="index.php?action=atualizarDep" method="POST" class="form-grupo">
    <input type="hidden" name="id" value="<?= $departamento['codDepartamento'] ?>">
    <label class="form-label">Nome do Departamento:</label><br>
    <input type="text" name="nome" class="form-input" required maxlength="50" value="<?= htmlspecialchars($departamento['nomeDepartamento']) ?>"><br>
    <button type="submit" class="btn-save">Salvar Alterações</button>
</form>

<?php include __DIR__ . '/include/rodape.php'; ?>
