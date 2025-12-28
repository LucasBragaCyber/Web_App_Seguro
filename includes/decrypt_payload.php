<?php
// includes/decrypt_payload.php

// Sessão já foi iniciada (pelo 'verifica_sessao.php')
// Faz a descriptografia dos payloads (ações como , alugar-livro favoritar-livro, devolver-livro, etc)
// Após, fornece uma variável dos dados descriptografados ($decrypted_data) para o script que inclui este arquivo

$dados_brutos = json_decode(file_get_contents("php://input"));

if (!isset($dados_brutos->encryptedKey) || !isset($dados_brutos->encryptedData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida. Dados criptografados esperados.']);
    exit;
}

$privateKeyPath = __DIR__ . '/chaves/privada.pem';
if (!file_exists($privateKeyPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro crítico: Chave do servidor não encontrada.']);
    exit;
}

$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
if ($privateKey === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar a chave do servidor.']);
    exit;
}

$encryptedKey = base64_decode($dados_brutos->encryptedKey);
if (openssl_private_decrypt($encryptedKey, $decryptedKeyJson, $privateKey) === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Falha ao descriptografar a chave de sessão.']);
    exit;
}

$keyData = json_decode($decryptedKeyJson, true);
$symmetricKey = hex2bin($keyData['key']);
$iv = hex2bin($keyData['iv']);

$encryptedData = base64_decode($dados_brutos->encryptedData);
$decryptedDataJson = openssl_decrypt($encryptedData, 'aes-256-cbc', $symmetricKey, OPENSSL_RAW_DATA, $iv);

if ($decryptedDataJson === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Falha ao descriptografar os dados da ação.']);
    exit;
}

// Sucesso! A variável $decrypted_data estará disponível para o script que incluiu este arquivo.
$decrypted_data = json_decode($decryptedDataJson, true);

?>