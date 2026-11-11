<?php
// Página pública de apresentação exibida antes do login.
    include __DIR__ . '/include/cabecalho.php';
?>

<!-- Área inicial com descrição do sistema e links de acesso. -->
<main class="conteudo-home">
    <section class="boas-vindas" style="text-align: center; padding: 40px 20px;">
        <h2 class="titulo-pagina" style="justify-content: center; margin-bottom: 20px;">Sistema Empresa</h2>

        <p class="texto-destaque" style="font-size: 1.2rem; margin-bottom: 20px;">
            Gestão de pessoas, departamentos, cargos e contas em um único sistema.
        </p>

        <div style="max-width: 760px; margin: 0 auto 30px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);">
            <h3 style="margin-bottom: 18px; color: #1e293b;">Bem-vindo ao projeto</h3>
            <p style="color: #475569; line-height: 1.8;">
                Este sistema foi desenvolvido para facilitar o cadastro e a administração de funcionários,
                departamentos, cargos e contas de acesso. A ideia é centralizar as operações de gestão em
                uma interface simples, organizada e segura.
            </p>
        </div>

        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; margin-top: 20px;">
            <a href="index.php?action=login" class="btn-save" style="display: inline-block; text-decoration: none; text-align: center;">Entrar no sistema</a>
        </div>
    </section>
</main>

<?php
    include __DIR__ . '/include/rodape.php';
?>
