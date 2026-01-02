<?php
// setup_db.php

// RODAR ESTE ARQUIVO UMA VEZ PARA GERAR O 'credenciais.enc' E DEPOIS APAGAR/PROTEGER.
// PROTEGER: Depois de rodar, colocar ele na pasta 'chaves', onde é protegido pelo .htaccess

// Para rodar o arquivo: http://localhost/BookShell/setup_db.php

// 1. Definição das credenciais de conexão ao banco
$credenciais = [
    'host'   => 'localhost',
    'user'   => 'braga',
    'pass'   => 'senha123',
    'dbname' => 'cadastro_bookshell'
];

// 2. Definição de uma chave mestra forte (descriptografia) (32 chars para AES-256)
// IMPORTANTE: Essa mesma chave deverá estar no db.php
define('CHAVE_MESTRA', 'xKZglAeweh1cMLTd8u3dASMjKhRinAeY');

// 3. Criptografia
$metodo = 'aes-256-cbc';
$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
$dadosJson = json_encode($credenciais);

$dadosCriptografados = openssl_encrypt($dadosJson, $metodo, CHAVE_MESTRA, 0, $iv);

// Vamos salvar o IV junto com o conteúdo (necessário para descriptografar)
// Formato: IV + DADOS
$payload = base64_encode($iv . $dadosCriptografados);

// 4. Salva no arquivo
if (file_put_contents(__DIR__ . '/credenciais.enc', $payload)) {
    echo "Arquivo 'credenciais.enc' gerado com sucesso! <br>";
    echo "Agora configure seu db.php e apague este arquivo setup_db.php.";
} else {
    echo "Erro ao gravar arquivo.";
}
?>