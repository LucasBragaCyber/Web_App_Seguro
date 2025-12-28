<?php
// api/devolver-livro.php

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/verifica_sessao.php';
require '../includes/db.php';

require '../includes/decrypt_payload.php';

// Garante que o método da requisição seja POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método não permitido
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

require '../includes/decrypt_payload.php';

// Valida se o ID do aluguel foi recebido
if (!isset($decrypted_data['id']) || !is_numeric($decrypted_data['id'])) {
    http_response_code(400); // Requisição inválida
    echo json_encode(['success' => false, 'message' => 'ID do aluguel é inválido ou não foi fornecido.']);
    exit;
}

$aluguel_id = $decrypted_data['id'];
$id_livro = null;

// Inicia uma transação para garantir a consistência dos dados
mysqli_begin_transaction($conn);

try {
    // 1. Primeiro, busca o ID do livro associado a este aluguel para podermos atualizá-lo depois
    $stmt_select = mysqli_prepare($conn, "SELECT id_livro FROM alugueis WHERE id = ? AND devolvido = 0");
    mysqli_stmt_bind_param($stmt_select, "i", $aluguel_id);
    mysqli_stmt_execute($stmt_select);
    $result_select = mysqli_stmt_get_result($stmt_select);
    
    if ($aluguel = mysqli_fetch_assoc($result_select)) {
        $id_livro = $aluguel['id_livro'];
    } else {
        // Se o aluguel não for encontrado, lança um erro
        throw new Exception('Aluguel não encontrado ou já devolvido.');
    }
    mysqli_stmt_close($stmt_select);

    // 2. Atualiza o status do aluguel para devolvido
    $stmt_update_aluguel = mysqli_prepare($conn, "UPDATE alugueis SET devolvido = 1, data_devolucao = NOW() WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update_aluguel, "i", $aluguel_id);
    if (!mysqli_stmt_execute($stmt_update_aluguel)) {
        throw new Exception('Falha ao registrar a devolução.');
    }
    mysqli_stmt_close($stmt_update_aluguel);
    
    // 3. Atualiza a disponibilidade do livro para que ele possa ser alugado novamente
    $stmt_update_livro = mysqli_prepare($conn, "UPDATE livros SET disponibilidade = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt_update_livro, "i", $id_livro);
    if (!mysqli_stmt_execute($stmt_update_livro)) {
        throw new Exception('Falha ao atualizar a disponibilidade do livro.');
    }
    mysqli_stmt_close($stmt_update_livro);

    // Se tudo deu certo, confirma as alterações no banco de dados
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Devolução confirmada com sucesso!']);

} catch (Exception $e) {
    // Se qualquer passo falhar, desfaz todas as alterações
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);

?>