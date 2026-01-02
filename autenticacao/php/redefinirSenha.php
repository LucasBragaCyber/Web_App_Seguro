<?php
// /BookShell/autenticacao/php/redefinirSenha.php

require '../../includes/db.php';

header('Content-Type: application/json');

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$token = $data['token'] ?? null;
$novaSenha = $data['senha'] ?? null;

if (!$token || !$novaSenha) {
    echo json_encode(["sucesso" => false, "mensagem" => "Dados incompletos."]);
    exit;
}

// 1. Validar Token (Verifica se existe e se a data de expiração é maior que AGORA)
$stmt = $conn->prepare("SELECT email FROM recuperar_senha WHERE token = ? AND expiracao > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["sucesso" => false, "mensagem" => "Link inválido ou expirado. Solicite novamente."]);
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];
$stmt->close();

// 2. Atualizar Senha na tabela de usuários
$novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
$update->bind_param("ss", $novaSenhaHash, $email);

if ($update->execute()) {
    // 3. Invalidar o token (Deletar da tabela recuperar_senha para não usar 2 vezes)
    $del = $conn->prepare("DELETE FROM recuperar_senha WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();

    echo json_encode(["sucesso" => true, "mensagem" => "Senha alterada com sucesso!"]);
} else {
    echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar senha no banco."]);
}
