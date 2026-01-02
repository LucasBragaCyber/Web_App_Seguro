// Captura o token da URL
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');

if (!token) {
    Swal.fire('Erro', 'Token inválido ou ausente.', 'error').then(() => {
        window.location.href = 'autenticacao.html';
    });
} else {
    document.getElementById('token').value = token;
}

document.getElementById('formNovaSenha').addEventListener('submit', async function (e) {
    e.preventDefault();

    const senha = document.getElementById('senha').value;
    const confirma = document.getElementById('confirmaSenha').value;

    if (senha !== confirma) {
        Swal.fire('Erro', 'As senhas não conferem!', 'warning');
        return;
    }

    try {
        const response = await fetch('../php/redefinirSenha.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token, senha: senha })
        });

        const result = await response.json();

        if (result.sucesso) {
            Swal.fire('Sucesso!', 'Senha alterada com sucesso.', 'success').then(() => {
                window.location.href = 'autenticacao.html';
            });
        } else {
            Swal.fire('Erro', result.mensagem, 'error');
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Erro', 'Ocorreu um erro na requisição.', 'error');
    }
});