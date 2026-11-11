<?php 
    // Formulário para inserir um novo cargo.
    include __DIR__ . '/include/cabecalho.php';
?>

    <h2>Cadastrar Cargo</h2>
    <!-- Os campos são enviados ao método salvar do CargoController. -->
    <form action="index.php?action=salvarCargo" method="POST" class="form-grupo">
        <label class="form-label">Nome do Cargo:</label><br>
        <input 
            type="text" 
            name="nome" 
            class="form-input" 
            required 
            maxlength="100"
            placeholder="Ex: Analista"
        > <br>
        <label class="form-label">Salário Base (R$):</label><br>
        <input 
            type="text" 
            inputmode="decimal"
            name="salario" 
            class="form-input" 
            required 
            maxlength="14"
            placeholder="R$ 0,00"
            data-mask="moeda"
        > <br>
        <button type="submit" class="btn-save">Gravar Cargo</button>
    </form>

<?php 
    include __DIR__."/include/rodape.php";
?>
