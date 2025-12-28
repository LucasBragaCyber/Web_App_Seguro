// fetch - JS/dashboard.js
async function sendEncryptedRequest(url, data) {
    // 1. OBTER A CHAVE PÚBLICA DO SERVIDOR
    const pubKeyResponse = await fetch('/BookShell/api/public_key.php');
    if (!pubKeyResponse.ok) throw new Error('Não foi possível obter a chave pública.');
    const pubKeyData = await pubKeyResponse.json();
    const publicKey = pubKeyData.publicKey;

    // 2. GERAR CHAVE SIMÉTRICA (AES) E IV ALEATÓRIOS
    const symmetricKey = CryptoJS.lib.WordArray.random(256 / 8);
    const iv = CryptoJS.lib.WordArray.random(128 / 8);

    // 3. CRIPTOGRAFAR OS DADOS DA AÇÃO
    const encryptedData = CryptoJS.AES.encrypt(JSON.stringify(data), symmetricKey, {
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
    if (encryptedKey === false) throw new Error('Falha ao criptografar a chave de sessão com RSA.');
    
    // 5. PREPARAR O PAYLOAD FINAL E ENVIAR
    const payload = {
        encryptedKey: encryptedKey,
        encryptedData: encryptedData.toString()
    };
    
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Funções para carregar os dados da API ao carregar a página
    carregarUsuario();
    carregarCatalogo();
    carregarAlugueis();
    carregarFavoritos();

    // Adiciona event listeners para ações (alugar, devolver, etc.)
    document.body.addEventListener('click', handleActionClick);
});

// Função para verificar a sessão e carregar dados do usuário
async function carregarUsuario() {
    try {
        const response = await fetch('/BookShell/api/usuario.php');
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/BookShell/autenticacao/html/autenticacao.html?erro_acesso=1';
            }
            throw new Error('Falha ao obter dados do usuário.');
        }
        const resultado = await response.json();

        // Acessar os dados dentro do objeto 'data'
        if(resultado.success) {
            document.getElementById('nome-usuario-desktop').textContent = resultado.data.nome;
            document.getElementById('nome-usuario-mobile').textContent = resultado.data.nome;
        } else {
            throw new Error('Não foi possível obter os dados do usuário do payload da API.');
        }
    } catch (error) {
        console.error('Erro ao carregar usuário:', error);
    }
}

// Função para carregar e exibir o catálogo de livros
async function carregarCatalogo() {
    try {
        const response = await fetch('/BookShell/api/catalogo.php');
        const livros = await response.json();
        const container = document.getElementById('catalogo-livros');
        container.innerHTML = ''; 

        if (livros.length === 0) {
            container.innerHTML = '<p>Nenhum livro disponível no momento.</p>';
            return;
        }

        livros.forEach(livro => {
            container.innerHTML += `
                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card mb-3">
                    <img src="${livro.capa}" class="card-img-top capa-livro" alt="Capa de ${livro.titulo}">
                    <div class="card-body">
                      <h5 class="card-title">${livro.titulo}</h5>
                      <p class="card-text">${livro.autor}</p>
                      <button class="btn btn-success px-3 my-2 action-btn" data-action="alugar" data-id="${livro.id}">Alugar</button>
                      <button class="btn btn-outline-warning action-btn" data-action="favoritar" data-id="${livro.id}">Favoritar</button>
                    </div>
                  </div>
                </div>
            `;
        });
    } catch (error) {
        console.error('Erro ao carregar o catálogo:', error);
    }
}

// Função para carregar e exibir os aluguéis do usuário
async function carregarAlugueis() {
    try {
        const response = await fetch('/BookShell/api/alugueis.php');
        const alugueis = await response.json();
        const container = document.getElementById('lista-alugueis');
        container.innerHTML = ''; 

        if (alugueis.length === 0) {
            container.innerHTML = '<tr><td colspan="3">Você não possui livros alugados.</td></tr>';
            return;
        }

        alugueis.forEach(aluguel => {
            container.innerHTML += `
                <tr>
                  <td>${aluguel.titulo}</td>
                  <td>${aluguel.autor}</td>
                  <td>
                    <button class="btn btn-primary action-btn" data-action="devolver" data-id="${aluguel.aluguel_id}">Confirmar Devolução</button>
                  </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error('Erro ao carregar aluguéis:', error);
    }
}

// Função para carregar e exibir os livros favoritos
async function carregarFavoritos() {
    try {
        const response = await fetch('/BookShell/api/favoritos.php');
        const favoritos = await response.json();
        const container = document.getElementById('lista-favoritos');
        container.innerHTML = ''; 

        if (favoritos.length === 0) {
            container.innerHTML = '<p>Você não tem livros favoritos.</p>';
            return;
        }

        favoritos.forEach(livro => {
            container.innerHTML += `
                <div class="col-md-3">
                  <div class="card mb-3">
                    <img src="${livro.capa}" class="card-img-top capa-livro" alt="Capa de ${livro.titulo}">
                    <div class="card-body">
                      <h5 class="card-title">${livro.titulo}</h5>
                      <p class="card-text">${livro.autor}</p>
                      <button class="btn btn-outline-danger btn-sm action-btn" data-action="remover-favorito" data-id="${livro.id}">Remover</button>
                    </div>
                  </div>
                </div>
            `;
        });
    } catch (error) {
        console.error('Erro ao carregar favoritos:', error);
    }
}

// Função para lidar com cliques nos botões de ação (alugar, favoritar, etc.)
async function handleActionClick(event) {
    if (!event.target.classList.contains('action-btn')) return;

    const action = event.target.dataset.action;
    const id = event.target.dataset.id;
    let url = '';
    
    switch(action) {
        case 'alugar': url = '/BookShell/api/alugar-livro.php'; break;
        case 'favoritar': url = '/BookShell/api/favoritar-livro.php'; break;
        case 'devolver': url = '/BookShell/api/devolver-livro.php'; break;
        case 'remover-favorito': url = '/BookShell/api/remover-favorito.php'; break;
        default: return;
    }

    try {
        // Aqui é onde acontece a criptografia da função na linha 2
        const response = await sendEncryptedRequest(url, { id: id });
        const result = await response.json();

        if (result.success) {
            carregarCatalogo();
            carregarAlugueis();
            carregarFavoritos();
        } else {
            alert('Erro: ' + result.message);
        }
    } catch (error) {
        console.error(`Erro ao executar a ação ${action}:`, error);
        alert('Ocorreu um erro de comunicação ao tentar ' + action);
    }
}