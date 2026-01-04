<?php
// /BookShell/autenticacao/php/redefinirSenha.php

require '../../includes/db.php';

header('Content-Type: application/json');

$response = ["sucesso" => false, "mensagem" => "Erro desconhecido."];

// Lê JSON
$input = file_get_contents("php://input");
$dados = json_decode($input);

// --- LÓGICA DE DESCRIPTOGRAFIA ---
if (isset($dados->encryptedKey) && isset($dados->encryptedData)) {

    // Caminho da chave privada (Atenção aos níveis de diretório)
    // Estamos em /BookShell/autenticacao/php/, a chave está em /BookShell/includes/chaves/
    $privateKeyPath = __DIR__ . '/../../includes/chaves/privada.pem';

    if (!file_exists($privateKeyPath)) {
        http_response_code(500);
        echo json_encode(["sucesso" => false, "mensagem" => "Erro interno: Chave de segurança não encontrada."]);
        exit;
    }

    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    if (!$privateKey) {
        http_response_code(500);
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao carregar chave de segurança."]);
        exit;
    }

    // 1. Descriptografar Chave Simétrica (RSA)
    $encryptedKey = base64_decode($dados->encryptedKey);
    if (openssl_private_decrypt($encryptedKey, $decryptedKeyJson, $privateKey) === false) {
        http_response_code(400);
        echo json_encode(["sucesso" => false, "mensagem" => "Falha na descriptografia da sessão."]);
        exit;
    }

    $keyData = json_decode($decryptedKeyJson, true);
    $symmetricKey = hex2bin($keyData['key']);
    $iv = hex2bin($keyData['iv']);

    // 2. Descriptografar Dados (AES)
    $encryptedData = base64_decode($dados->encryptedData);
    $decryptedDataJson = openssl_decrypt($encryptedData, 'aes-256-cbc', $symmetricKey, OPENSSL_RAW_DATA, $iv);

    if ($decryptedDataJson === false) {
        http_response_code(400);
        echo json_encode(["sucesso" => false, "mensagem" => "Falha na descriptografia dos dados."]);
        exit;
    }

    $dadosDecifrados = json_decode($decryptedDataJson, true);
    $token = $dadosDecifrados['token'] ?? null;
    $novaSenha = $dadosDecifrados['senha'] ?? null;

    // --- VALIDAÇÃO DE SEGURANÇA DA SENHA ---
    // Mesmo validando no JS, OBRIGATÓRIO validar no Back-end
    $temMaiuscula = preg_match('/[A-Z]/', $novaSenha);
    $temNumero = preg_match('/[0-9]/', $novaSenha);
    $temMinimo8 = strlen($novaSenha) >= 8;

    if (!$temMaiuscula || !$temNumero || !$temMinimo8) {
        echo json_encode(["sucesso" => false, "mensagem" => "A senha deve ter no mínimo 8 caracteres, 1 letra maiúscula e 1 número."]);
        exit;
    }

    if (!$token) {
        echo json_encode(["sucesso" => false, "mensagem" => "Token não fornecido."]);
        exit;
    }

    // --- PROCESSO DE ATUALIZAÇÃO NO BANCO ---

    // 1. Validar Token
    $stmt = $conn->prepare("SELECT email FROM recuperar_senha WHERE token = ? AND expiracao > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["sucesso" => false, "mensagem" => "Link inválido ou expirado."]);
        exit;
    }

    $row = $result->fetch_assoc();
    $email = $row['email'];
    $stmt->close();

    // 2. Atualizar Senha (Hash)
    $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
    $update->bind_param("ss", $novaSenhaHash, $email);

    if ($update->execute()) {
        // 3. Consumir Token
        $del = $conn->prepare("DELETE FROM recuperar_senha WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();

        echo json_encode(["sucesso" => true, "mensagem" => "Senha alterada com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar senha no banco."]);
    }
} else {
    // Caso receba dados sem criptografia (tentativa de bypass ou erro)
    http_response_code(400);
    echo json_encode(["sucesso" => false, "mensagem" => "Requisição inválida. Criptografia obrigatória."]);
}
