<?php
// Formulário de edição de funcionário com seus vínculos.
include __DIR__ . '/include/cabecalho.php'; ?>

<h2>Editar Funcionário</h2>
<!-- A chave funcional identifica o funcionário que será alterado. -->
<form action="index.php?action=atualizarFunc" method="POST" class="form-grupo">
    <input type="hidden" name="funcional" value="<?= $funcionario['funcional'] ?>">
    <label class="form-label">Nome do Funcionário:</label><br>
    <input type="text" name="nome" class="form-input" required maxlength="40" value="<?= htmlspecialchars($funcionario['nome']) ?>"><br>
    <label class="form-label">CPF:</label><br>
    <input type="text" name="cpf" class="form-input" required maxlength="14" inputmode="numeric" data-mask="cpf" value="<?= htmlspecialchars($funcionario['cpf']) ?>"><br>
    <label class="form-label">Telefone:</label><br>
    <input type="text" name="telefone" class="form-input" maxlength="15" inputmode="tel" data-mask="telefone" value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"><br>
    <fieldset class="endereco-campos">
        <legend>Endereço</legend>
        <label class="form-label" for="cep">CEP</label>
        <input type="text" id="cep" name="endereco[cep]" class="form-input" required maxlength="9" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" data-mask="cep" value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>" aria-describedby="mensagemCep">
        <small id="mensagemCep" class="cep-status" role="status" aria-live="polite">Saia do campo CEP para consultar ou preencha o endereço manualmente.</small>
        <label class="form-label" for="logradouro">Logradouro</label>
        <input type="text" id="logradouro" name="endereco[logradouro]" class="form-input" required maxlength="150" autocomplete="address-line1" value="<?= htmlspecialchars($funcionario['logradouro'] ?? '') ?>">
        <div class="endereco-linha">
            <div>
                <label class="form-label" for="numero">Número</label>
                <input type="text" id="numero" name="endereco[numero]" class="form-input" required maxlength="20" value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label" for="complemento">Complemento</label>
                <input type="text" id="complemento" name="endereco[complemento]" class="form-input" maxlength="100" value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>">
            </div>
        </div>
        <label class="form-label" for="bairro">Bairro</label>
        <input type="text" id="bairro" name="endereco[bairro]" class="form-input" required maxlength="100" autocomplete="address-line2" value="<?= htmlspecialchars($funcionario['bairro'] ?? '') ?>">
        <div class="endereco-linha">
            <div>
                <label class="form-label" for="cidade">Cidade</label>
                <input type="text" id="cidade" name="endereco[cidade]" class="form-input" required maxlength="100" autocomplete="address-level2" value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label" for="uf">UF</label>
                <input type="text" id="uf" name="endereco[uf]" class="form-input" required maxlength="2" pattern="[A-Za-z]{2}" autocomplete="address-level1" value="<?= htmlspecialchars($funcionario['uf'] ?? '') ?>">
            </div>
        </div>
    </fieldset>
    <label class="form-label">Departamento:</label><br>
    <select name="codDepartamento" class="form-input" required>
        <?php foreach ($listaDep as $departamento): ?>
            <option value="<?= $departamento['codDepartamento'] ?>" <?= $departamento['codDepartamento'] == $funcionario['codDepartamento'] ? 'selected' : '' ?>><?= htmlspecialchars($departamento['nomeDepartamento']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <label class="form-label">Cargo:</label><br>
    <select name="codCargo" class="form-input" required>
        <?php foreach ($listaCargo as $cargo): ?>
            <option value="<?= $cargo['codCargo'] ?>" <?= $cargo['codCargo'] == $funcionario['codCargo'] ? 'selected' : '' ?>><?= htmlspecialchars($cargo['nomeCargo']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <button type="submit" class="btn-save">Salvar Alterações</button>
</form>

<?php include __DIR__ . '/include/rodape.php'; ?>
