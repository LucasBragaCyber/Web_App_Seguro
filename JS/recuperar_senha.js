document.getElementById('formRecuperacao').addEventListener('submit', async function (e) {
    e.preventDefault();

    const emailInput = document.getElementById('email');
    const email = emailInput.value;
    const btn = this.querySelector('button');
    // Salva o texto original do botão ("Enviar código")
    const textoOriginal = "Enviar código";
    // 1. Estado de Carregamento
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verificando...';
    btn.disabled = true;

    try {
        const response = await fetch('../../api/recuperarSenha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });

        // Tenta converter a resposta em JSON
        const result = await response.json();

        if (result.sucesso) {
            // === CASO DE SUCESSO ===
            // Substitui o formulário pela mensagem de confirmação
            const form = document.getElementById('formRecuperacao');
            form.innerHTML = `
                    <div class="text-center animate__animated animate__fadeIn" style="width: 100%;">
                        <span class="login100-form-title bookshell-fonte3 py-3">
                            Verifique seu E-mail
                        </span>
                        
                        <div class="my-4">
                            <i class="bi bi-envelope-check-fill text-success" style="font-size: 5rem;"></i>
                        </div>

                        <p class="text-white mb-4 fonte-3" style="font-size: 1.1rem;">
                            Enviamos um link de recuperação para:<br>
                            <strong>${email}</strong>
                        </p>
                        
                        <div class="container-login100-form-btn">
                            <a href="./autenticacao.html" class="login100-form-btn fonte-2 text-decoration-none text-white">
                                Voltar ao Login
                            </a>
                        </div>
                    </div>
                `;
        } else {
            // === CASO DE ERRO (E-mail não existe, etc) ===
            Swal.fire({
                icon: 'warning', // Ícone de alerta amarelo (melhor que erro vermelho para validação)
                title: 'Atenção',
                text: result.mensagem, // Aqui virá: "E-mail informado inválido ou não existe"
                confirmButtonColor: '#d33'
            });

            // RESTAURA O BOTÃO
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }

    } catch (error) {
        // === CASO DE ERRO TÉCNICO (Servidor fora, bug no PHP) ===
        console.error(error);
        Swal.fire('Erro', 'Ocorreu um erro ao processar sua solicitação.', 'error');

        // RESTAURA O BOTÃO TAMBÉM AQUI
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    }
});