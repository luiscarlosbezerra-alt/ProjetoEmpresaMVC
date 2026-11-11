<?php 
    // Formulário de cadastro de funcionário e seus relacionamentos.
    include __DIR__ . '/include/cabecalho.php';
?>
    <h2>Cadastrar Funcionário</h2>
    <!-- Os selects usam listas de cargos e departamentos carregadas pelo roteador. -->
    <form action="index.php?action=salvarFunc" method="POST" class="form-grupo">
        <label class="form-label">Nome do Funcionário:</label><br>
        <input 
            type="text" 
            name="nome" 
            class="form-input" 
            required 
            maxlength="40"
            placeholder="Ex: Luís Carlos"
        > <br>
        <label class="form-label">CPF:</label><br>
        <input 
            type="text" 
            name="cpf" 
            class="form-input" 
            required 
            placeholder="000.000.000-00"
            maxlength="14"
            inputmode="numeric"
            data-mask="cpf"
            title="Digite um CPF com 11 números"
        > <br>
        <label class="form-label">Telefone:</label><br>
        <input 
            type="text"  
            name="telefone" 
            class="form-input" 
            placeholder="(11) 94484-9414"
            maxlength="15"
            inputmode="tel"
            data-mask="telefone"
        > <br>
        <fieldset class="endereco-campos">
            <legend>Endereço</legend>
            <label class="form-label" for="cep">CEP</label>
            <input type="text" id="cep" name="endereco[cep]" class="form-input" required maxlength="9" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" data-mask="cep" aria-describedby="mensagemCep">
            <small id="mensagemCep" class="cep-status" role="status" aria-live="polite">Saia do campo CEP para consultar ou preencha o endereço manualmente.</small>
            <label class="form-label" for="logradouro">Logradouro</label>
            <input type="text" id="logradouro" name="endereco[logradouro]" class="form-input" required maxlength="150" autocomplete="address-line1">
            <div class="endereco-linha">
                <div>
                    <label class="form-label" for="numero">Número</label>
                    <input type="text" id="numero" name="endereco[numero]" class="form-input" required maxlength="20">
                </div>
                <div>
                    <label class="form-label" for="complemento">Complemento</label>
                    <input type="text" id="complemento" name="endereco[complemento]" class="form-input" maxlength="100">
                </div>
            </div>
            <label class="form-label" for="bairro">Bairro</label>
            <input type="text" id="bairro" name="endereco[bairro]" class="form-input" required maxlength="100" autocomplete="address-line2">
            <div class="endereco-linha">
                <div>
                    <label class="form-label" for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="endereco[cidade]" class="form-input" required maxlength="100" autocomplete="address-level2">
                </div>
                <div>
                    <label class="form-label" for="uf">UF</label>
                    <input type="text" id="uf" name="endereco[uf]" class="form-input" required maxlength="2" pattern="[A-Za-z]{2}" autocomplete="address-level1">
                </div>
            </div>
        </fieldset>
        <label class="form-label">Departamento:</label><br>
        <select name="codDepartamento" class="form-input" required>
            <option value="">Selecione...</option>
            <?php foreach($listaDep as $d): ?>
                <option value="<?= $d['codDepartamento']?>">
                    <?= $d['nomeDepartamento']?></option>
            <?php endforeach;?>
        </select><br>
        <label class="form-label">Cargo:</label><br>
        <select name="codCargo" class="form-input" required>
            <option value="">Selecione...</option>
            <?php foreach($listaCargo as $d): ?>
                <option value="<?= $d['codCargo']?>">
                    <?= $d['nomeCargo']?></option>
            <?php endforeach;?>
        </select><br>
        <button type="submit" class="btn-save">Cadastrar Funcionário</button>
    </form>

<?php 
    include __DIR__."/include/rodape.php";
?>
