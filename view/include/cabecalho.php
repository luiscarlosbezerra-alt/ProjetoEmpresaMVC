<?php
// Cabeçalho compartilhado: inicia a sessão, carrega o CSS e monta a navegação.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recupera o alerta criado pelo controller e o remove após esta exibição.
$alerta = $_SESSION['alerta'] ?? null;
unset($_SESSION['alerta']);
$nomeCompleto = trim((string) ($_SESSION['usuario_nome'] ?? 'Usuário'));
$primeiroNome = htmlspecialchars(explode(' ', $nomeCompleto)[0] ?: 'Usuário', ENT_QUOTES, 'UTF-8');
$perfilConta = ($_SESSION['usuario_perfil'] ?? '') === 'admin' ? 'Administrador' : 'Funcionário';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema Empresa</title>

    <link rel="stylesheet" href="assets/css/estilo.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- Menus administrativos aparecem somente para usuários autenticados como admin. -->
<header>
    <h1>Sistema Empresa</h1>

    <nav>

        <?php if (isset($_SESSION['usuario_funcional'])): ?>

            <?php if (isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'admin'): ?>

                <div class="dropdown">
                    <span class="menu-item">Cargos</span>
                    <div class="dropdown-content">
                        <a href="index.php?action=formCargo">Novo cargo</a>
                        <a href="index.php?action=listaCargo">Listar cargos</a>
                    </div>
                </div>

                <div class="dropdown">
                    <span class="menu-item">Departamentos</span>
                    <div class="dropdown-content">
                        <a href="index.php?action=formDep">Novo departamento</a>
                        <a href="index.php?action=listaDep">Listar departamentos</a>
                    </div>
                </div>

                <div class="dropdown">
                    <span class="menu-item">Funcionários</span>
                    <div class="dropdown-content">
                        <a href="index.php?action=formFunc">Novo funcionário</a>
                        <a href="index.php?action=listaFunc">Listar funcionários</a>
                    </div>
                </div>

                <div class="dropdown">
                    <span class="menu-item">Contas</span>
                    <div class="dropdown-content">
                        <a href="index.php?action=formConta">Nova conta</a>
                        <a href="index.php?action=listaConta">Listar contas</a>
                    </div>
                </div>

            <?php endif; ?>

            <div class="dropdown">
                <span class="menu-item">Solicitações</span>
                <div class="dropdown-content">
                    <a href="index.php?action=solicitacoes"><?= ($_SESSION['usuario_perfil'] ?? '') === 'admin' ? 'Analisar pedidos' : 'Minhas solicitações' ?></a>
                    <?php if (($_SESSION['usuario_perfil'] ?? '') !== 'admin'): ?>
                        <a href="index.php?action=novaSolicitacao">Novo pedido</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="account-menu">
                <button type="button" id="accountMenuButton" class="account-menu-trigger" aria-label="Abrir opções da conta" aria-haspopup="true" aria-expanded="false" aria-controls="accountMenuDropdown" title="Opções da conta">
                    <svg class="account-trigger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span><?= $primeiroNome ?></span>
                    <svg class="account-trigger-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m7 10 5 5 5-5"></path>
                    </svg>
                </button>

                <div class="account-dropdown" id="accountMenuDropdown" hidden>
                    <div class="account-dropdown-heading">
                        <span class="account-dropdown-avatar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <span class="account-dropdown-info">
                            <strong><?= htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8') ?></strong>
                            <small><?= htmlspecialchars($perfilConta, ENT_QUOTES, 'UTF-8') ?></small>
                        </span>
                    </div>
                    <a class="account-dropdown-action" href="index.php?action=relatorioPerfil">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path>
                        </svg>
                        <span>Editar conta</span>
                    </a>
                    <a class="account-dropdown-action account-dropdown-logout" href="index.php?action=logout" onclick="return confirmarSaida(event, this.href)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m10 17 5-5-5-5"></path>
                            <path d="M15 12H3"></path>
                            <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"></path>
                        </svg>
                        <span>Sair da conta</span>
                    </a>
                </div>
            </div>

        <?php else: ?>

            <div class="dropdown">
                <span class="menu-item">Acesso</span>

                <div class="dropdown-content">
                    <a href="index.php?action=login">Entrar</a>
                </div>
            </div>

        <?php endif; ?>
    </nav>
</header>

<?php
$alertasValidos = ['success', 'error', 'warning', 'info'];
$tipoAlerta = is_array($alerta) && in_array($alerta['icon'] ?? '', $alertasValidos, true)
    ? $alerta['icon']
    : 'error';
?>
<?php if (is_array($alerta) && isset($alerta['text'])): ?>
    <div class="flash-message flash-message-<?= htmlspecialchars($tipoAlerta, ENT_QUOTES, 'UTF-8') ?>"
         role="<?= $tipoAlerta === 'error' ? 'alert' : 'status' ?>"
         aria-live="<?= $tipoAlerta === 'error' ? 'assertive' : 'polite' ?>">
        <span class="flash-message-icon" aria-hidden="true"><?= $tipoAlerta === 'success' ? '&#10003;' : '!' ?></span>
        <div>
            <?php if (!empty($alerta['title'])): ?>
                <strong><?= htmlspecialchars((string) $alerta['title'], ENT_QUOTES, 'UTF-8') ?></strong>
            <?php endif; ?>
            <p><?= htmlspecialchars((string) $alerta['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="container">

<?php if (isset($_SESSION['usuario_funcional']) && ($_GET['action'] ?? '') !== 'home'): ?>
    <div class="navegacao-voltar-inicio">
        <a href="index.php?action=home">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M5 12h14"></path>
            </svg>
            <span>Voltar para início</span>
        </a>
    </div>
<?php elseif (in_array($_GET['action'] ?? '', ['login', 'formConta'], true)): ?>
    <div class="navegacao-voltar-inicio">
        <a href="index.php?action=apresentacao">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m12 19-7-7 7-7"></path>
                <path d="M5 12h14"></path>
            </svg>
            <span>Voltar à apresentação</span>
        </a>
    </div>
<?php endif; ?>

<!-- Confirma a saída antes de destruir a sessão. -->
<script>
function confirmarSaida(event, url) {
    event.preventDefault();

    Swal.fire({
        title: 'Deseja sair?',
        text: 'Sua sessão será encerrada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, sair',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });

    return false;
}

function confirmarExclusao(event, formulario, tipo) {
    // Substitui a confirmação nativa por uma caixa visual padronizada.
    event.preventDefault();

    Swal.fire({
        title: 'Confirmar exclusão?',
        text: 'O registro de ' + tipo + ' será removido.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((resultado) => {
        if (resultado.isConfirmed) {
            formulario.submit();
        }
    });

    return false;
}

function confirmarAlteracoes(event, formulario, descricao) {
    event.preventDefault();
    Swal.fire({
        icon: 'question',
        title: 'Confirmar alterações?',
        text: 'Deseja salvar as alterações ' + descricao + '?',
        showCancelButton: true,
        confirmButtonText: 'Sim, salvar',
        cancelButtonText: 'Revisar',
        confirmButtonColor: '#2563eb'
    }).then((resultado) => {
        if (resultado.isConfirmed) {
            formulario.submit();
        }
    });
    return false;
}

const accountMenuButton = document.getElementById('accountMenuButton');
const accountMenuDropdown = document.getElementById('accountMenuDropdown');

if (accountMenuButton && accountMenuDropdown) {
    const fecharMenuConta = () => {
        accountMenuDropdown.hidden = true;
        accountMenuButton.setAttribute('aria-expanded', 'false');
    };

    accountMenuButton.addEventListener('click', (event) => {
        event.stopPropagation();
        const abrir = accountMenuDropdown.hidden;
        accountMenuDropdown.hidden = !abrir;
        accountMenuButton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
        if (!accountMenuDropdown.contains(event.target) && !accountMenuButton.contains(event.target)) {
            fecharMenuConta();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !accountMenuDropdown.hidden) {
            fecharMenuConta();
            accountMenuButton.focus();
        }
    });
}
</script>
