<?php 
    // Formulário de cadastro de departamentos.
    include __DIR__ . '/include/cabecalho.php';
?>

    <h2>Cadastrar Departamento</h2>
    <!-- Envia o nome para o DepController. -->
    <form action="index.php?action=salvarDep" method="POST" class="form-grupo">
        <label class="form-label">Nome do Departamento:</label><br>
        <input 
            type="text" 
            name="nome" 
            class="form-input" 
            required 
            maxlength="50"
            placeholder="Ex: Recursos Humanos"
        > <br>
        <button type="submit" class="btn-save">Gravar Departamento</button>
    </form>

<?php 
    include __DIR__."/include/rodape.php";
?>
