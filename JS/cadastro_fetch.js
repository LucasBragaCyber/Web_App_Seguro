document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.getElementById('formulario');
    const emailInput = document.getElementById('email');
    const telefoneInput = document.getElementById('phone');
    const nomeInput = document.getElementById('nome');
    const senhaInput = document.getElementById('senha');
    const confirmarSenhaInput = document.getElementById('confirmarSenha');
    const mensagemGeralAPI = document.getElementById('mensagem-geral-api');
    const confirmarSenhaMensagem = document.getElementById('mensagem-confirmar');
    const botaoCadastro = document.getElementById('botao-cadastro');
    // A lógica de toggle de senha pode continuar a mesma...

    if (formulario) {
        formulario.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (mensagemGeralAPI) mensagemGeralAPI.textContent = '';
            if (mensagemGeralAPI) mensagemGeralAPI.className = 'text-center py-2';
            if (confirmarSenhaMensagem) confirmarSenhaMensagem.textContent = '';

            const senha = senhaInput.value;
            const confirmarSenha = confirmarSenhaInput.value;

            if (senha !== confirmarSenha) {
                if (confirmarSenhaMensagem) {
                    confirmarSenhaMensagem.textContent = 'As senhas não coincidem!';
                    confirmarSenhaMensagem.style.color = 'red';
                }
                return;
            }

            const plainData = {
                nome: nomeInput.value.trim(),
                email: emailInput.value.trim(),
                telefone: telefoneInput.value.trim(),
                senha: senha
            };

            if (botaoCadastro) botaoCadastro.disabled = true;
            if (mensagemGeralAPI) mensagemGeralAPI.textContent = 'Criptografando...';

            try {
                // --- INÍCIO DA LÓGICA DE CRIPTOGRAFIA HÍBRIDA ---

                // 1. OBTER A CHAVE PÚBLICA DO SERVIDOR
                const pubKeyResponse = await fetch('/BookShell/api/public_key.php');
                if (!pubKeyResponse.ok) throw new Error('Não foi possível obter a chave pública.');
                const pubKeyData = await pubKeyResponse.json();
                const publicKey = pubKeyData.publicKey;

                // 2. GERAR CHAVE SIMÉTRICA (AES) E IV ALEATÓRIOS
                const symmetricKey = CryptoJS.lib.WordArray.random(256 / 8);
                const iv = CryptoJS.lib.WordArray.random(128 / 8);

                // 3. CRIPTOGRAFAR OS DADOS DO FORMULÁRIO COM AES-256-CBC
                const encryptedData = CryptoJS.AES.encrypt(JSON.stringify(plainData), symmetricKey, {
                    iv: iv,
                    mode: CryptoJS.mode.CBC,
                    padding: CryptoJS.pad.Pkcs7
                });

                // 4. CRIPTOGRAFAR A CHAVE SIMÉTRICA E O IV COM A CHAVE PÚBLICA (RSA)
                const encryptor = new JSEncrypt();
                encryptor.setPublicKey(publicKey);
                
                const keyAndIv = {
                    key: CryptoJS.enc.Hex.stringify(symmetricKey),
                    iv: CryptoJS.enc.Hex.stringify(iv)
                };
                const encryptedKey = encryptor.encrypt(JSON.stringify(keyAndIv));
                
                if (encryptedKey === false) {
                    throw new Error('Falha ao criptografar a chave de sessão com RSA.');
                }
                
                // 5. PREPARAR O PAYLOAD FINAL
                const payload = {
                    encryptedKey: encryptedKey,
                    encryptedData: encryptedData.toString()
                };

                // --- FIM DA LÓGICA DE CRIPTOGRAFIA ---
                
                if (mensagemGeralAPI) mensagemGeralAPI.textContent = 'Processando...';

                // CORREÇÃO: O caminho para a API de registro é 'api/registrar.php'
                const response = await fetch('/BookShell/api/registrar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const resultado = await response.json();

                if (response.ok) {
                    if (mensagemGeralAPI) {
                        mensagemGeralAPI.textContent = resultado.mensagem;
                        mensagemGeralAPI.className = 'text-center py-2 text-success';
                    }
                    formulario.reset();
                    setTimeout(() => {
                        window.location.href = '/BookShell/autenticacao/html/autenticacao.html?cadastro=sucesso';
                    }, 3000);
                } else {
                    if (mensagemGeralAPI) {
                        mensagemGeralAPI.textContent = resultado.mensagem || `Erro: ${response.status}`;
                        mensagemGeralAPI.className = 'text-center py-2 text-danger';
                    }
                }
            } catch (error) {
                console.error('Erro na requisição de cadastro:', error);
                if (mensagemGeralAPI) {
                    mensagemGeralAPI.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
                    mensagemGeralAPI.className = 'text-center py-2 text-danger';
                }
            } finally {
                if (botaoCadastro) botaoCadastro.disabled = false;
            }
        });
    }
     const toggleSenha = document.getElementById('toggleSenha');
     const toggleConfirmar = document.getElementById('toggleConfirmar');
 
     if (toggleSenha) {
         toggleSenha.addEventListener('click', function () {
             const tipo = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
             senhaInput.setAttribute('type', tipo);
             this.classList.toggle('bi-eye-slash-fill');
         });
     }
 
     if (toggleConfirmar) {
         toggleConfirmar.addEventListener('click', function () {
             const tipo = confirmarSenhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
             confirmarSenhaInput.setAttribute('type', tipo);
             this.classList.toggle('bi-eye-slash-fill');
         });
     }
});