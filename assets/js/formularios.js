(() => {
    const onlyDigits = (value) => value.replace(/\D/g, '');

    const formatters = {
        cpf(value) {
            const digits = onlyDigits(value).slice(0, 11);
            return digits
                .replace(/^(\d{3})(\d)/, '$1.$2')
                .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');
        },
        data(value) {
            const digits = onlyDigits(value).slice(0, 8);
            return digits
                .replace(/^(\d{2})(\d)/, '$1/$2')
                .replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
        },
        telefone(value) {
            const digits = onlyDigits(value).slice(0, 11);
            if (digits.length < 3) {
                return digits ? `(${digits}` : '';
            }
            const ddd = digits.slice(0, 2);
            const numero = digits.slice(2);
            if (numero.length <= 4) {
                return `(${ddd}) ${numero}`;
            }
            if (numero.length <= 8) {
                return `(${ddd}) ${numero.slice(0, 4)}-${numero.slice(4)}`;
            }
            return `(${ddd}) ${numero.slice(0, 5)}-${numero.slice(5)}`;
        },
        moeda(value) {
            const digits = onlyDigits(value);
            if (!digits) {
                return '';
            }
            const valor = digits.replace(/^0+(?=\d)/, '').padStart(3, '0');
            const inteiro = valor.slice(0, -2);
            const decimais = valor.slice(-2);
            const formatado = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return `R$ ${formatado},${decimais}`;
        }
    };

    document.querySelectorAll('[data-mask]').forEach((input) => {
        const format = formatters[input.dataset.mask];
        if (!format) {
            return;
        }
        input.addEventListener('input', () => {
            input.value = format(input.value);
        });
        if (input.value) {
            input.value = format(input.value);
        }
    });

    const formulariosComData = new Set();
    document.querySelectorAll('[data-mask="data"]').forEach((input) => {
        input.type = 'text';
        input.inputMode = 'numeric';
        input.placeholder = 'dd/mm/aaaa';
        input.maxLength = 10;

        const status = document.createElement('p');
        status.className = 'date-mask-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.id = `${input.id || input.name}-status`;
        input.insertAdjacentElement('afterend', status);
        input.setAttribute('aria-describedby', status.id);

        input.addEventListener('input', () => {
            input.setCustomValidity('');
            status.textContent = '';
        });

        if (input.form) {
            formulariosComData.add(input.form);
        }
    });

    formulariosComData.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const camposData = [...form.querySelectorAll('[data-mask="data"]')];
            const datasConvertidas = [];

            for (const input of camposData) {
                const status = document.getElementById(`${input.id || input.name}-status`);
                const digits = onlyDigits(input.value);
                let mensagem = '';

                if (digits.length !== 8) {
                    mensagem = 'Informe a data completa no formato dd/mm/aaaa.';
                } else {
                    const dia = Number(digits.slice(0, 2));
                    const mes = Number(digits.slice(2, 4));
                    const ano = Number(digits.slice(4, 8));
                    const data = new Date(0);
                    data.setFullYear(ano, mes - 1, dia);
                    data.setHours(0, 0, 0, 0);

                    if (ano < 1000 || data.getFullYear() !== ano || data.getMonth() !== mes - 1 || data.getDate() !== dia) {
                        mensagem = 'A data informada não existe.';
                    } else {
                        datasConvertidas.push({
                            input,
                            valor: `${digits.slice(4, 8)}-${digits.slice(2, 4)}-${digits.slice(0, 2)}`
                        });
                    }
                }

                input.setCustomValidity(mensagem);
                if (status) {
                    status.textContent = mensagem;
                }
                if (mensagem) {
                    event.preventDefault();
                    input.reportValidity();
                    return;
                }
            }

            for (const { input, valor } of datasConvertidas) {
                input.value = valor;
            }
        });
    });

})();
