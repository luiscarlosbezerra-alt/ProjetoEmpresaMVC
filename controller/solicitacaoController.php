<?php
require_once __DIR__ . '/../model/solicitacaoModel.php';

class SolicitacaoController
{
    public function listar()
    {
        $model = new Solicitacao();
        $ehAdmin = ($_SESSION['usuario_perfil'] ?? '') === 'admin';
        $pedidos = $ehAdmin
            ? $model->listarTodas()
            : $model->listarDoFuncionario($_SESSION['usuario_funcional']);
        $csrfToken = $this->obterTokenCsrf();
        include __DIR__ . '/../view/solicitacoes.php';
    }

    public function formulario()
    {
        $csrfToken = $this->obterTokenCsrf();
        include __DIR__ . '/../view/solicitacaoForm.php';
    }

    public function salvar()
    {
        if (!$this->validarPost()) {
            return;
        }

        $tipo = $_POST['tipo'] ?? '';
        $inicioTexto = trim((string) ($_POST['dataInicio'] ?? ''));
        $fimTexto = trim((string) ($_POST['dataFim'] ?? ''));
        $motivo = trim((string) ($_POST['motivo'] ?? ''));
        $inicio = $this->dataValida($inicioTexto);
        $fim = $this->dataValida($fimTexto);

        if (!in_array($tipo, ['ferias', 'ausencia'], true) || !$inicio || !$fim
            || $inicioTexto > $fimTexto || $motivo === '' || strlen($motivo) > 500) {
            $this->alerta('error', 'Confira os dados', 'Informe o tipo, período válido e um motivo de até 500 caracteres.');
            $this->redirecionar('novaSolicitacao');
        }

        $hojeNoBrasil = (new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
        if ($tipo === 'ferias' && $inicioTexto < $hojeNoBrasil) {
            $this->alerta('error', 'Período de férias inválido', 'O início das férias deve ser hoje ou uma data futura.');
            $this->redirecionar('novaSolicitacao');
        }

        try {
            (new Solicitacao())->criar(
                $_SESSION['usuario_funcional'],
                $tipo,
                $inicioTexto,
                $fimTexto,
                $motivo
            );
            $this->alerta('success', 'Pedido enviado', 'Você pode acompanhar a análise na lista de solicitações.');
        } catch (RuntimeException $erro) {
            $this->alerta('error', 'Não foi possível enviar', $erro->getMessage());
            $this->redirecionar('novaSolicitacao');
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            $this->alerta('error', 'Erro ao enviar pedido', 'Tente novamente. Se o problema continuar, avise um administrador.');
        }

        $this->redirecionar('solicitacoes');
    }

    public function avaliar()
    {
        if (!$this->validarPost()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $resposta = trim((string) ($_POST['respostaAdmin'] ?? ''));
        if ($id === false || $id < 1 || !in_array($status, ['aprovada', 'recusada'], true)
            || strlen($resposta) > 500 || ($status === 'recusada' && $resposta === '')) {
            $this->alerta('error', 'Análise inválida', 'Informe uma decisão válida; ao recusar, explique o motivo.');
            $this->redirecionar('solicitacoes');
        }

        try {
            $atualizado = (new Solicitacao())->avaliar(
                $id,
                $status,
                $resposta,
                $_SESSION['usuario_funcional'],
                $_SESSION['usuario_nome'] ?? 'Administrador'
            );
            if ($atualizado) {
                $texto = $status === 'aprovada' ? 'O pedido foi aprovado.' : 'O pedido foi recusado.';
                $this->alerta('success', 'Análise registrada', $texto);
            } else {
                $this->alerta('warning', 'Pedido já analisado', 'Atualize a página para ver o estado atual.');
            }
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            $this->alerta('error', 'Erro ao analisar', 'Não foi possível registrar a decisão.');
        }

        $this->redirecionar('solicitacoes');
    }

    public function cancelar()
    {
        if (!$this->validarPost()) {
            return;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $cancelado = $id !== false && $id > 0
            && (new Solicitacao())->cancelar($id, $_SESSION['usuario_funcional']);

        if ($cancelado) {
            $this->alerta('success', 'Pedido cancelado', 'A solicitação pendente foi cancelada.');
        } else {
            $this->alerta('warning', 'Não foi possível cancelar', 'Somente pedidos pendentes do seu usuário podem ser cancelados.');
        }
        $this->redirecionar('solicitacoes');
    }

    private function dataValida($valor)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)
            || (int) substr($valor, 0, 4) < 1000
            || (int) substr($valor, 0, 4) > 9999) {
            return null;
        }

        $data = DateTime::createFromFormat('!Y-m-d', $valor);
        return $data && $data->format('Y-m-d') === $valor ? $data : null;
    }

    private function validarPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirecionar('solicitacoes');
        }

        $tokenSessao = $_SESSION['solicitacao_csrf'] ?? '';
        $tokenEnviado = $_POST['csrf_token'] ?? '';
        if (!is_string($tokenEnviado) || $tokenSessao === '' || !hash_equals($tokenSessao, $tokenEnviado)) {
            $this->alerta('error', 'Sessão expirada', 'Atualize a página e tente novamente.');
            $this->redirecionar('solicitacoes');
        }

        return true;
    }

    private function obterTokenCsrf()
    {
        if (empty($_SESSION['solicitacao_csrf'])) {
            $_SESSION['solicitacao_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['solicitacao_csrf'];
    }

    private function alerta($icon, $title, $text)
    {
        $_SESSION['alerta'] = compact('icon', 'title', 'text');
    }

    private function redirecionar($action)
    {
        header('Location: index.php?action=' . rawurlencode($action));
        exit;
    }
}
