
// Função para gerar hash SHA-256 usando CryptoJS
function hashPassword(senha) {
    // Gera o hash SHA-256
    const hash = CryptoJS.SHA256(senha);
    
    // Converte para string hexadecimal
    return hash.toString(CryptoJS.enc.Hex);
}

// Manipulador do formulário
document.getElementById('formulario').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Coleta os dados do formulário
    const email = document.getElementById('email').value;
    const telefone = document.getElementById('phone').value;
    const nome = document.getElementById('nome').value;
    const senha = document.getElementById('senha').value;
    
    // Gera o hash da senha
    const hashSenha = hashPassword(senha);
    
    // Cria o objeto usuário
    const usuario = {
        email: email,
        telefone: telefone,
        nome: nome,
        senhaHash: hashSenha
    };
 
    // Aqui enviaria os dados para o servidor
    console.log('Dados do usuário:', usuario);
    
    // Limpa o formulário
    this.reset();
    
    console.log('Cadastro realizado com sucesso!\nSenha com hash: ' +hashSenha);
});