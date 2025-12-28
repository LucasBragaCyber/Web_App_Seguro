document.addEventListener('DOMContentLoaded', function () {
    // Lógica para verificar parâmetros da URL e mostrar toast de notificação da página
    const urlParams = new URLSearchParams(window.location.search);
    let notificationMessage = null;

    if (urlParams.has('expirado') && urlParams.get('expirado') === '1') {
        notificationMessage = "Sua sessão expirou por inatividade. Faça login novamente.";
    } else if (urlParams.has('erro_acesso') && urlParams.get('erro_acesso') === '1') {
        notificationMessage = "Você precisa estar autenticado para acessar esta página.";
    }

    if (notificationMessage) {
        const toastElement = document.getElementById('pageNotificationToast');
        const toastBody = document.getElementById('pageNotificationToastBody');
        if (toastElement && toastBody) {
            toastBody.textContent = notificationMessage;
            const bootstrapToast = new bootstrap.Toast(toastElement);
            bootstrapToast.show();
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
    
    //Loógica para o formulário de login
    const formLogin = document.getElementById('formLogin');
    if (formLogin) {
        formLogin.addEventListener('submit', async function (event) {
            event.preventDefault();

            const btnLogin = document.getElementById('btnLogin');
            const mensagemLoginApi = document.getElementById('mensagemLoginApi');
            
            mensagemLoginApi.textContent = '';

            const plainData = {
                email: document.getElementById('email').value.trim(),
                senha: document.getElementById('password').value
            };

            if (plainData.email === '' || plainData.senha === '') {
                mensagemLoginApi.textContent = 'Por favor, preencha email e senha.';
                return;
            }

            btnLogin.disabled = true;
            mensagemLoginApi.textContent = 'Criptografando...';

            try {
                // --- INÍCIO DA LÓGICA DE CRIPTOGRAFIA HÍBRIDA ---

                // 1. OBTER A CHAVE PÚBLICA DO SERVIDOR
                const pubKeyResponse = await fetch('/BookShell/api/public_key.php');
                if (!pubKeyResponse.ok) throw new Error('Não foi possível obter a chave pública do servidor.');
                const pubKeyData = await pubKeyResponse.json();
                const publicKey = pubKeyData.publicKey;

                // 2. GERAR CHAVE SIMÉTRICA (AES) E VETOR DE INICIALIZAÇÃO (IV) ALEATÓRIOS
                // Usamos 256 bits para a chave (32 bytes) e 128 bits para o IV (16 bytes)
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
                    key: CryptoJS.enc.Hex.stringify(symmetricKey), // Envia em formato Hex
                    iv: CryptoJS.enc.Hex.stringify(iv)
                };
                // Converte o objeto em uma única string JSON e criptografa com RSA
                const encryptedKey = encryptor.encrypt(JSON.stringify(keyAndIv));
                
                if (encryptedKey === false) {
                    throw new Error('Falha ao criptografar a chave de sessão com RSA.');
                }
                
                // 5. PREPARAR O PAYLOAD FINAL E ENVIAR
                const payload = {
                    encryptedKey: encryptedKey, // Chave simétrica+IV criptografados com RSA
                    encryptedData: encryptedData.toString() // Dados do form criptografados com AES
                };

                mensagemLoginApi.textContent = 'Processando...';
                
                const response = await fetch('/BookShell/api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                // --- FIM DA LÓGICA DE CRIPTOGRAFIA ---

                const resultado = await response.json();

                if (response.ok && resultado.status === "sucesso") {
                    window.location.href = '/BookShell/autenticacao/html/autenticado.html';
                } else { 
                    mensagemLoginApi.textContent = resultado.mensagem || `Erro: ${response.status}`;
                }
            } catch (error) {
                console.error('Erro no processo de login:', error);
                mensagemLoginApi.textContent = 'Ocorreu um erro de comunicação. Tente novamente.';
            } finally {
                btnLogin.disabled = false;
            }
        });
    }
});