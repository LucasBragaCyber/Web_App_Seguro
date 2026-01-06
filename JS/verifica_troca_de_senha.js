document.addEventListener('DOMContentLoaded', function () {
    // 1. Captura o token da URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (!token) {
        Swal.fire('Erro', 'Token inválido ou ausente.', 'error').then(() => {
            window.location.href = 'autenticacao.html';
        });
        return;
    }

    document.getElementById('token').value = token;

    // 2. Listener do Formulário
    document.getElementById('formNovaSenha').addEventListener('submit', async function (e) {
        e.preventDefault();

        const senhaInput = document.getElementById('senha');
        const confirmaInput = document.getElementById('confirmaSenha');
        const msgErro = document.getElementById('mensagem-erro');
        const btn = this.querySelector('button');

        const senha = senhaInput.value;
        const confirma = confirmaInput.value;

        // Limpa mensagens anteriores
        msgErro.textContent = '';

        // --- VALIDAÇÃO DE SENHA (Igual ao Cadastro) ---
        // Pelo menos 8 caracteres, 1 maiúscula, 1 número
        const regexSenha = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

        if (senha !== confirma) {
            msgErro.textContent = 'As senhas não conferem.';
            return;
        }

        if (!regexSenha.test(senha)) {
            msgErro.textContent = 'A senha deve ter no mínimo 8 caracteres, 1 letra maiúscula e 1 número.';
            return;
        }

        // Feedback visual
        const textoOriginal = btn.innerHTML;
        btn.innerHTML = 'Processando...';
        btn.disabled = true;

        try {
            // --- INÍCIO DA CRIPTOGRAFIA HÍBRIDA ---

            // 1. Obter chave pública
            const pubKeyResponse = await fetch('/BookShell/api/public_key.php');
            if (!pubKeyResponse.ok) throw new Error('Erro ao obter chave pública.');
            const pubKeyData = await pubKeyResponse.json();
            const publicKey = pubKeyData.publicKey;

            // 2. Dados puros (Token + Nova Senha)
            const plainData = {
                token: token,
                senha: senha
            };

            // 3. Gerar chaves simétricas
            const symmetricKey = CryptoJS.lib.WordArray.random(256 / 8);
            const iv = CryptoJS.lib.WordArray.random(128 / 8);

            // 4. Criptografar dados com AES
            const encryptedData = CryptoJS.AES.encrypt(JSON.stringify(plainData), symmetricKey, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            });

            // 5. Criptografar a chave simétrica com RSA
            const encryptor = new JSEncrypt();
            encryptor.setPublicKey(publicKey);

            const keyAndIv = {
                key: CryptoJS.enc.Hex.stringify(symmetricKey),
                iv: CryptoJS.enc.Hex.stringify(iv)
            };
            const encryptedKey = encryptor.encrypt(JSON.stringify(keyAndIv));

            if (!encryptedKey) throw new Error('Falha na criptografia RSA.');

            const payload = {
                encryptedKey: encryptedKey,
                encryptedData: encryptedData.toString()
            };

            // --- FIM DA CRIPTOGRAFIA ---

            // Envio para o Backend
            const response = await fetch('../../api/redefinirSenha.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.sucesso) { // Note: Backend deve retornar 'sucesso' (bool)
                Swal.fire('Sucesso!', 'Senha alterada com sucesso.', 'success').then(() => {
                    window.location.href = 'autenticacao.html';
                });
            } else {
                Swal.fire('Erro', result.mensagem, 'error');
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
            btn.innerHTML = textoOriginal;
            btn.disabled = false;
        }
    });
});