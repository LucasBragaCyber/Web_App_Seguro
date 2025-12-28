<?php
$imageFile = __DIR__ . "/hidden.jpg"; // Caminho absoluto
$marker = "--CRED--";

// Lê conteúdo binário da imagem
$data = file_get_contents($imageFile);
if ($data === false) {
    die("Erro ao ler a imagem.");
}

// Garante tipo string
$data = strval($data);

// Localiza o marcador
$pos = strpos($data, $marker);
if ($pos === false) {
    die("Credenciais não encontradas na imagem.");
}

// Extrai e limpa o JSON
$json = substr($data, $pos + strlen($marker));
$json = preg_replace('/[^[:print:]]/', '', $json);
$creds = json_decode(trim($json), true);

if (!$creds) {
    die("Falha ao decodificar credenciais.");
}

// Conecta ao banco
$conn = new mysqli($creds['host'], $creds['user'], $creds['pass'], $creds['dbname']);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// echo "✅ Conectado com sucesso ao banco! ✅";
?>
