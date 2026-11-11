<?php
include __DIR__ . '/include/cabecalho.php';
$csrfToken = $csrfToken ?? '';
?>

<p class="home-kicker">SERVIÇOS DO FUNCIONÁRIO</p>
<h2>Nova solicitação</h2>
<p class="solicitacao-intro">Envie um pedido de férias ou registre uma ausência. O administrador analisará a solicitação e você poderá acompanhar a resposta nesta área.</p>

<form action="index.php?action=salvarSolicitacao" method="POST" class="form-grupo solicitacao-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

    <label class="form-label" for="tipo">Tipo de solicitação</label>
    <select class="form-input" id="tipo" name="tipo" required>
        <option value="">Selecione...</option>
        <option value="ferias">Férias</option>
        <option value="ausencia">Ausência</option>
    </select>

    <div class="solicitacao-datas">
        <div>
            <label class="form-label" for="dataInicio">Data inicial</label>
            <input class="form-input" id="dataInicio" type="text" name="dataInicio" required maxlength="10" inputmode="numeric" placeholder="dd/mm/aaaa" data-mask="data">
        </div>
        <div>
            <label class="form-label" for="dataFim">Data final</label>
            <input class="form-input" id="dataFim" type="text" name="dataFim" required maxlength="10" inputmode="numeric" placeholder="dd/mm/aaaa" data-mask="data">
        </div>
    </div>

    <label class="form-label" for="motivo">Motivo ou observação</label>
    <textarea class="form-input solicitacao-motivo" id="motivo" name="motivo" maxlength="500" required placeholder="Informe os detalhes necessários para a análise"></textarea>

    <div class="solicitacao-acoes-form">
        <button type="submit" class="btn-save">Enviar solicitação</button>
        <a href="index.php?action=solicitacoes">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/include/rodape.php'; ?>
