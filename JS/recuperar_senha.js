document.getElementById('formRecuperacao').addEventListener('submit', async function (e) {
    e.preventDefault(); // Impede o recarregamento

    const emailInput = document.getElementById('email');
    const email = emailInput.value;
    const btn = this.querySelector('button');
    const textoOriginal = btn.innerHTML;
    const form = document.getElementById('formRecuperacao'); // Pegamos o formulário inteiro

    // Feedback visual de carregamento
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';
    btn.disabled = true;

    try {
        const response = await fetch('../php/recuperarSenha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });

        const result = await response.json();

        if (result.sucesso) {
            // SUCESSO: Substitui o formulário pela mensagem de confirmação
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
                        
                        <p class="text-muted mb-4 small">
                            (Verifique também sua caixa de Spam)
                        </p>

                        <div class="container-login100-form-btn">
                            <a href="./autenticacao.html" class="login100-form-btn fonte-2 text-decoration-none text-white">
                                Fazer Login
                            </a>
                        </div>
                    </div>
                `;
        } else {
            // ERRO: Mantém o SweetAlert para avisos rápidos
            Swal.fire({
                icon: 'error',
                title: 'Atenção',
                text: result.mensagem,
                confirmButtonColor: '#d33'
            });
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
        btn.innerHTML = textoOriginal;
        btn.disabled = false;
    }
});