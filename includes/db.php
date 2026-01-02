<?php
// db.php

// Define a chave mestra (Deve ser IGUAL a usada no setup_db.php)

define('DB_KEY', 'xKZglAeweh1cMLTd8u3dASMjKhRinAeY');

$arquivoCredenciais = __DIR__ . '/../../BookShell/credenciais.enc';

if (!file_exists($arquivoCredenciais)) {
    die("Erro Crítico: Arquivo de configuração de banco de dados ausente.");
}

// 1. Ler o conteúdo criptografado
$payload = file_get_contents($arquivoCredenciais);
$dadosBinarios = base64_decode($payload);

// 2. Extrair IV e Dados
$metodo = 'aes-256-cbc';
$ivLength = openssl_cipher_iv_length($metodo);
$iv = substr($dadosBinarios, 0, $ivLength);
$dadosCriptografados = substr($dadosBinarios, $ivLength);

// 3. Descriptografar
$json = openssl_decrypt($dadosCriptografados, $metodo, DB_KEY, 0, $iv);
$creds = json_decode($json, true);

if (!$creds) {
    die("Erro Crítico: Falha ao descriptografar credenciais do banco.");
}

// 4. Conectar usando MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($creds['host'], $creds['user'], $creds['pass'], $creds['dbname']);
    $conn->set_charset("utf8mb4"); // Importante para caracteres especiais
} catch (mysqli_sql_exception $e) {
    // Log do erro real no servidor (error_log) e mensagem genérica para o usuário
    error_log($e->getMessage()); 
    die("Erro de conexão com o banco de dados."); 
}

// A variável $conn está pronta para uso!
?>