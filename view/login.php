<?php 
    // Formulário que envia login e senha para a action de autenticação.
    include __DIR__ . '/include/cabecalho.php';
?>

    <h2>Login</h2>
    <!-- A validação final das credenciais ocorre no controller. -->
    <form action="index.php?action=autenticar" method="POST" class="form-grupo">
        <label class="form-label">Login:</label><br>
        <input type="text" name="login" class="form-input" required placeholder="Digite seu login"><br>

        <label class="form-label">Senha:</label><br>
        <input type="password" name="senha" class="form-input" required placeholder="Digite sua senha"><br>

        <button type="submit" class="btn-save">Entrar</button>
    </form>

<?php 
    include __DIR__."/include/rodape.php";
?>
