(() => {
    const cep = document.querySelector('[data-mask="cep"]');
    const mensagem = document.getElementById('mensagemCep');
    if (!cep || !mensagem) {
        return;
    }

    const campos = {
        logradouro: document.getElementById('logradouro'),
        bairro: document.getElementById('bairro'),
        cidade: document.getElementById('cidade'),
        uf: document.getElementById('uf')
    };
    let consultaAtual = 0;
    let entradaComLetras = false;

    const formatarCep = (valor) => valor.replace(/\D/g, '').slice(0, 8).replace(/^(\d{5})(\d)/, '$1-$2');
    cep.value = formatarCep(cep.value);

    cep.addEventListener('beforeinput', (evento) => {
        if (evento.inputType.startsWith('insert')) {
            const inserido = evento.data ?? evento.dataTransfer?.getData('text') ?? '';
            if (inserido !== '') {
                entradaComLetras = /[^\d\s.-]/.test(inserido);
            }
        } else if (evento.inputType.startsWith('delete')) {
            entradaComLetras = false;
        }
    });

    cep.addEventListener('input', () => {
        cep.value = formatarCep(cep.value);
        consultaAtual++;
        mensagem.textContent = 'Saia do campo CEP para consultar ou preencha o endereço manualmente.';
    });

    cep.addEventListener('blur', async () => {
        const consulta = ++consultaAtual;
        const digitos = cep.value.replace(/\D/g, '');

        if (entradaComLetras) {
            mensagem.textContent = 'O CEP não pode conter letras. Informe somente os 8 números.';
            return;
        }
        if (!/^\d{8}$/.test(digitos)) {
            mensagem.textContent = 'CEP incompleto ou inválido. Informe os 8 números para consultar.';
            return;
        }

        mensagem.textContent = 'Consultando CEP...';
        const controlador = new AbortController();
        const temporizador = window.setTimeout(() => controlador.abort(), 10000);

        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${digitos}/json/`, {
                signal: controlador.signal
            });
            if (!resposta.ok) {
                throw new Error(`ViaCEP respondeu com status ${resposta.status}.`);
            }

            const dados = await resposta.json();
            if (consulta !== consultaAtual) {
                return;
            }
            if (dados.erro === true) {
                mensagem.textContent = 'CEP não encontrado. Confira o número ou preencha o endereço manualmente.';
                return;
            }
            if (!dados.logradouro && !dados.bairro && !dados.localidade && !dados.uf) {
                throw new Error('A resposta do ViaCEP não contém um endereço válido.');
            }

            if (campos.logradouro) campos.logradouro.value = dados.logradouro || '';
            if (campos.bairro) campos.bairro.value = dados.bairro || '';
            if (campos.cidade) campos.cidade.value = dados.localidade || '';
            if (campos.uf) campos.uf.value = dados.uf || '';
            mensagem.textContent = 'Endereço encontrado. Confira os dados e complete manualmente se necessário.';
        } catch (erro) {
            if (consulta === consultaAtual) {
                mensagem.textContent = 'Não foi possível consultar o CEP. Verifique sua conexão e preencha o endereço manualmente.';
            }
            console.error('Falha na consulta ao ViaCEP:', erro);
        } finally {
            window.clearTimeout(temporizador);
        }
    });
})();
