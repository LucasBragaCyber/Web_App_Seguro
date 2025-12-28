const campos = [
    {
        id: 'email',
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        errorMessage: 'E-mail inválido!'
    },
    {
        id: 'phone',
        regex: /^\(\d{2}\)\d{5}-\d{4}$/,
        errorMessage: 'Telefone inválido! Use o formato (xx)xxxxx-xxxx'
    },
    {
        id: 'nome',
        regex: /^[A-Za-zÀ-ÿ\s]+$/,
        errorMessage: 'Não use números ou símbolos.'
    },
    {
        id: 'senha',
        regex: /^(?=.*[A-Z])(?=.*\d).{8,}$/,
        errorMessage: 'Senha inválida! Mín. 8 caracteres, 1 letra maiúscula e 1 número.'
    }
];

campos.forEach(campo => {
    const input = document.getElementById(campo.id);
    const message = document.getElementById(`${campo.id}-mensagem`);

    function feedbackVisual() {
        const value = input.value.trim();

        if (value === '') {
            input.className = 'vazio';
        } else if (campo.regex.test(value)) {
            input.className = 'valido';
        } else {
            input.className = 'invalido';
        }
    }

    function mostraErro() {
        const value = input.value.trim();
        if (value !== '' && !campo.regex.test(value)) {
          message.textContent = campo.errorMessage;
          message.style.visibility = 'visible';
          message.style.opacity = '1';
        } else {
          message.textContent = '';
          message.style.visibility = 'hidden';
          message.style.opacity = '0';
        }
      }

    // Máscara de telefone
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function (e) {
        let value = phoneInput.value.replace(/\D/g, ''); // Remove não números
        if (value.length > 11) value = value.slice(0, 11);

        const formatado = value
            .replace(/^(\d{2})(\d)/, '($1)$2')       // (xx)
            .replace(/(\d{5})(\d)/, '$1-$2');        // xxxxx-xxxx

        phoneInput.value = formatado;
    });

    input.addEventListener('input', feedbackVisual);
    input.addEventListener('blur', mostraErro);

    // Validação inicial (sem mensagem)
    feedbackVisual();
});
