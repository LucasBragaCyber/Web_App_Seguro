<?php
// api/public_key.php
// ENDPOINT que fornece a chave pública
header("Content-Type: application/json; charset=UTF-8");

$publicKeyPath = __DIR__ . '/../includes/chaves/publica.pem';

if (file_exists($publicKeyPath)) {
    $publicKey = file_get_contents($publicKeyPath);
    echo json_encode(['publicKey' => $publicKey]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Chave pública não encontrada.']);
}
?>