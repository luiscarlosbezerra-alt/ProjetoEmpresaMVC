<?php
// Formulário de administração de login, nível e senha.
include __DIR__ . '/include/cabecalho.php'; ?>
<?php $conta = $conta ?? []; ?>
<?php $editandoPropriaConta = $editandoPropriaConta ?? false; ?>

<h2>Editar Conta de Usuário</h2>
<!-- Senha vazia significa que a senha atual será preservada. -->
<form action="index.php?action=atualizarConta" method="POST" enctype="multipart/form-data" class="form-grupo" onsubmit="return confirmarAlteracoes(event, this, 'da conta')">
    <input type="hidden" name="funcional" value="<?= $conta['funcional'] ?>">
    <p>Funcionário: <strong><?= htmlspecialchars($conta['nome']) ?></strong></p>
    <label class="form-label">Login:</label><br>
    <input type="text" name="login" class="form-input" required maxlength="100" value="<?= htmlspecialchars($conta['login']) ?>"><br>
    <label class="form-label">Nível de acesso:</label><br>
    <select name="nivelAcesso" class="form-input" required>
        <option value="1" <?= $conta['nivelAcesso'] == 1 ? 'selected' : '' ?>>Funcionário</option>
        <option value="0" <?= $conta['nivelAcesso'] == 0 ? 'selected' : '' ?>>Administrador</option>
    </select><br>
    <label class="form-label">Foto de perfil (opcional):</label><br>
    <input type="file" name="foto" class="form-input" accept="image/jpeg, image/png, image/webp"><br>
    <?php if ($editandoPropriaConta): ?>
        <p class="conta-aviso-senha">A senha da sua própria conta deve ser alterada em Meu perfil.</p>
    <?php else: ?>
        <label class="form-label">Nova senha (deixe em branco para manter):</label><br>
        <input type="password" name="senha" class="form-input" minlength="8" maxlength="72"><br>
    <?php endif; ?>
    <button type="submit" class="btn-save">Salvar Alterações</button>
</form>

<?php include __DIR__ . '/include/rodape.php'; ?>
