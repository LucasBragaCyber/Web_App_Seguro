<?php
// includes/verifica_sessao.php

// Inicia a sessão se ainda não foi iniciada, para ter acesso às variáveis de sessão.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define o tempo limite de inatividade em segundos (1 hora = 3600 segundos) para expiração de sessão
// Para testar se está funcionando, com 10 segundos
//$inactive_timeout = 10;
$inactive_timeout = 3600;


// Verifica se a variável de última atividade está definida
if (isset($_SESSION['LAST_ACTIVITY'])) {
    // Calcula o tempo de inatividade
    $inactive_time = time() - $_SESSION['LAST_ACTIVITY'];

    // Se o tempo de inatividade for maior que o tempo limite...
    if ($inactive_time > $inactive_timeout) {
        // Destroi todos os dados da sessão
        $_SESSION = array();
        session_destroy();

        // Obtém o caminho base do projeto
        $baseUrl = '/BookShell';

        // Prepara o URL de redirecionamento para a página de login
        $redirect_url = $baseUrl . "/autenticacao/html/autenticacao.html?expirado=1";
        
        // Se a requisição for AJAX/Fetch, envia uma resposta 401 (Não Autorizado)
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            http_response_code(401);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Sessão expirada por inatividade.']);
        } else {
            // Para requisições normais, redireciona o navegador
            header("Location: " . $redirect_url);
        }
        exit;
    }
}

// Atualiza o timestamp da última atividade para a hora atual a cada requisição bem-sucedida
$_SESSION['LAST_ACTIVITY'] = time();

?>