<?php
// api/favoritar_livro.php

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Inclui os scripts de verificação de sessão e conexão com o banco
require_once __DIR__ . '/../includes/verifica_sessao.php';
require '../includes/db.php';

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

// 1. VERIFICAR SE O FAVORITO JÁ EXISTE PARA EVITAR DUPLICATAS
$check_sql = "SELECT id FROM favoritos WHERE id_usuario = ? AND id_livro = ?";
$stmt_check = $conn->prepare($check_sql);
$stmt_check->bind_param("ii", $id_usuario, $id_livro);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    http_response_code(409); // Conflito
    echo json_encode(['success' => false, 'message' => 'Este livro já está nos seus favoritos.']);
    $stmt_check->close();
    $conn->close();
    exit;
}
$stmt_check->close();

// 2. INSERIR O NOVO FAVORITO
$insert_sql = "INSERT INTO favoritos (id_usuario, id_livro) VALUES (?, ?)";
$stmt_insert = $conn->prepare($insert_sql);

// Verifica se a preparação da query falhou
if ($stmt_insert === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao preparar a query.']);
    $conn->close();
    exit;
}

// Associa os parâmetros e seus tipos ('i' para integer)
$stmt_insert->bind_param("ii", $id_usuario, $id_livro);

// Executa a query e retorna o resultado
if ($stmt_insert->execute()) {
    echo json_encode(['success' => true, 'message' => 'Livro favoritado com sucesso!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Não foi possível favoritar o livro.']);
}

// Fecha o statement e a conexão
$stmt_insert->close();
$conn->close();
?>