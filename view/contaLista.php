<?php
// Lista contas de acesso para administração.
include __DIR__ . '/include/cabecalho.php'; ?>
<?php $temOutroAdministrador = $temOutroAdministrador ?? false; ?>

<h2>Relatório de Contas de Usuários</h2>

    <!-- Cada linha permite editar ou remover a conta correspondente. -->
    <table class="tabela-dados">
    <thead>
        <tr>
            <th>Foto</th>
            <th>Funcional</th>
            <th>Funcionário</th>
            <th>Login</th>
            <th>Nível de acesso</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($listaContas) && count($listaContas) > 0): ?>
            <?php foreach ($listaContas as $conta): ?>
                <tr>
                    <td style="text-align: center;">
                        <?php
                            $nomeFoto = basename((string) ($conta['foto'] ?? ''));
                            $foto = dirname(__DIR__) . '/assets/img/perfil/' . $nomeFoto;
                            $caminhoFoto = file_exists($foto)
                                ? 'assets/img/perfil/' . rawurlencode($nomeFoto)
                                : 'assets/img/perfil/default-avatar.svg';
                        ?>
                        <img src="<?= htmlspecialchars($caminhoFoto, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Perfil" 
                            class="foto-perfil-lista">
                    </td>
                    <td>#<?= $conta['funcional'] ?></td>
                    <td><?= htmlspecialchars($conta['nome']) ?></td>
                    <td><?= htmlspecialchars($conta['login']) ?></td>
                    <td><?= $conta['nivelAcesso'] == 0 ? 'Administrador' : 'Funcionário' ?></td>
                    <td class="acoes-tabela">
                        <a class="btn-acao btn-editar" href="index.php?action=editarConta&id=<?=$conta['funcional']?>">Editar</a>
                        <?php if ((int) $conta['funcional'] === (int) ($_SESSION['usuario_funcional'] ?? 0)
                            && (int) $conta['nivelAcesso'] === 0 && !$temOutroAdministrador): ?>
                            <span class="conta-admin-protegido" title="Cadastre outro administrador para poder excluir esta conta.">Último administrador</span>
                        <?php else: ?>
                            <form class="form-exclusao" action="index.php?action=excluirConta" method="post" onsubmit="return confirmarExclusao(event, this, 'conta');">
                                <input type="hidden" name="id" value="<?= (int) $conta['funcional'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(TokenCsrf::obter(), ENT_QUOTES, 'UTF-8') ?>">
                                <button class="btn-acao btn-excluir" type="submit">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center;" class="msg-vazia">Nenhuma conta cadastrada no sistema.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/include/rodape.php'; ?>
