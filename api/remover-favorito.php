<?php
// api/remover_favorito.php

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Inclui os scripts de verificação de sessão e conexão com o banco
require_once __DIR__ . '/../includes/verifica_sessao.php';            // Garante que o usuário está logado
require '../includes/db.php';

require '../includes/decrypt_payload.php';

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método não permitido
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Utilize POST.']);
    exit;
}

// Obtém os dados enviados no corpo da requisição (em formato JSON)
require '../includes/decrypt_payload.php';

// Valida os dados de entrada
if (!isset($decrypted_data['id']) || !is_numeric($decrypted_data['id'])) {
    http_response_code(400); // Requisição inválida
    echo json_encode(['success' => false, 'message' => 'ID do livro ausente ou inválido.']);
    exit;
}

$id_livro = (int) $decrypted_data['id'];
$id_usuario = (int) $_SESSION['usuario']['id'];

// Prepara a query SQL para deletar o favorito
$sql = "DELETE FROM favoritos WHERE id_usuario = ? AND id_livro = ?";
$stmt = $conn->prepare($sql);

// Verifica se a preparação da query falhou
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao preparar a query.']);
    $conn->close();
    exit;
}

// Associa os parâmetros e seus tipos ('i' para integer)
$stmt->bind_param("ii", $id_usuario, $id_livro);

// Executa a query e retorna o resultado
if ($stmt->execute()) {
    // Verifica se alguma linha foi realmente afetada
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Favorito removido com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Favorito não encontrado para remover.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível remover o favorito.']);
}

// Fecha o statement e a conexão
$stmt->close();
$conn->close();
?>