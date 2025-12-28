// js/autenticado.js

// Executa a função principal quando o conteúdo do HTML for totalmente carregado
document.addEventListener('DOMContentLoaded', () => {
    carregarDadosUsuario();
});

/**
 * Busca os dados do usuário na API e atualiza a página.
 * Se o usuário não estiver autenticado, redireciona para a página de login.
 */
async function carregarDadosUsuario() {
    try {
        // Faz uma requisição para o nosso endpoint de API que retorna os dados da sessão
        const response = await fetch('/BookShell/api/usuario.php');

        // Se a resposta da API indicar "Não Autorizado" (status 401),
        // o usuário não está logado. Redirecionamos para a página de login.
        if (response.status === 401) {
            window.location.href = '/BookShell/autenticacao/html/autenticacao.html?erro_acesso=1';
            return; // Interrompe a execução
        }
        
        // Se a requisição falhar por outros motivos, lança um erro
        if (!response.ok) {
            throw new Error('Falha na comunicação com o servidor.');
        }

        // Converte a resposta da API (que está em JSON) para um objeto JavaScript
        const resultado = await response.json();

        // Se a API retornar sucesso, preenchemos os dados do usuário na página
        if (resultado.success) {
            document.getElementById('usuario-nome').textContent = resultado.data.nome;
            document.getElementById('usuario-email').textContent = resultado.data.email;
        } else {
            // Se a API retornar um erro conhecido, exibe a mensagem
            throw new Error(resultado.message);
        }

    } catch (error) {
        // Em caso de qualquer erro, exibe no console e atualiza a página com uma mensagem de erro.
        console.error('Erro ao carregar dados do usuário:', error);
        const cardBody = document.querySelector('.card-body');
        cardBody.innerHTML = `<p class="text-danger">Não foi possível carregar os dados. Tente fazer o <a href="/BookShell/autenticacao/html/autenticacao.html">login</a> novamente.</p>`;
    }
}