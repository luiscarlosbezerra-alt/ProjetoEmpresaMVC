<?php 
    // Formulário administrativo de criação de conta.
    include __DIR__."/include/cabecalho.php";
?>

    <h2>Criar Conta de Usuário</h2>
    <!-- Somente administradores acessam esta action pelo roteador. -->
    <form action="index.php?action=salvarConta" method="POST" enctype="multipart/form-data" class="form-grupo">
        <label class="form-label" for="funcional">Funcionário:</label><br>
        <select id="funcional" name="funcional" class="form-input" required>
            <option value="">Selecione...</option>
            <?php foreach ($funcionariosDisponiveis as $funcionario): ?>
                <option value="<?= (int) $funcionario['funcional'] ?>">
                    <?= htmlspecialchars($funcionario['nome']) ?> (#<?= (int) $funcionario['funcional'] ?>)
                </option>
            <?php endforeach; ?>
        </select><br>
        <label class="form-label">Login de Acesso desejado:</label><br>
        <input 
            type="text"  
            name="login" 
            class="form-input" 
            required
            maxlength="100"
            placeholder="Ex: nome.sobrenome"
        > <br>
        <label class="form-label" for="nivelAcesso">Nível de acesso:</label><br>
        <select id="nivelAcesso" name="nivelAcesso" class="form-input" required>
            <option value="1">Funcionário</option>
            <option value="0">Administrador</option>
        </select><br>
        <label class="form-label">Senha do Sistema:</label><br>
        <input 
            type="password"  
            name="senha" 
            class="form-input" 
            required 
            minlength="8"
            maxlength="72"
            placeholder="Define sua senha de acesso"
        > <br>
        <label class="form-label">Foto de Perfil:</label><br>
        <input 
            type="file"  
            name="foto" 
            class="form-input" 
            accept="image/jpeg, image/png, image/webp"
        > <br>
        <button type="submit" class="btn-save" <?= empty($funcionariosDisponiveis) ? 'disabled' : '' ?>>Criar Conta</button>
        <?php if (empty($funcionariosDisponiveis)): ?>
            <p>Todos os funcionários já possuem conta de acesso.</p>
        <?php endif; ?>
    </form>

<?php 
    include __DIR__."/include/rodape.php";
?>
