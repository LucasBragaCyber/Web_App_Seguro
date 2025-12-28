const campos = [
    {
        id: 'email',
        regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        errorMessage: 'E-mail inválido!'
    },
    // {
    //     id: 'password',
    //     regex: /^(?=.*[A-Z])(?=.*\d).{8,}$/,
    //     errorMessage: 'Senha inválida! Obs.: Mínimo 8 caracteres, 1 letra maiúscula e 1 número.'
    // }
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

    input.addEventListener('input', feedbackVisual);
    input.addEventListener('blur', mostraErro);

    // Validação inicial (sem mensagem)
    feedbackVisual();
});
