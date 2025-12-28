<?php
// api/usuario.php
// A sessão é iniciada neste arquivo
require_once __DIR__ . '/../includes/verifica_sessao.php';

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Verifica se a variável de sessão 'usuario' e seus dados existem
if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['nome']) && isset($_SESSION['usuario']['email'])) {
    
    // Se o usuário está logado, retorna uma resposta de sucesso com seus dados
    echo json_encode([
        'success' => true,
        'data' => [
            'nome' => $_SESSION['usuario']['nome'],
            'email' => $_SESSION['usuario']['email']
        ]
    ]);

} else {
    // Se o usuário não está logado, envia o código de status HTTP 401 (Não Autorizado)
    http_response_code(401);
    
    // Retorna uma mensagem de erro em JSON
    echo json_encode([
        'success' => false, 
        'message' => 'Acesso não autorizado. Por favor, faça o login.'
    ]);
}
?>